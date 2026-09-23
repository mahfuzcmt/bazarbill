<?php

namespace App\Services;

use App\Models\Market;
use App\Models\Plan;
use App\Models\SmsCreditTransaction;
use App\Models\Subscription;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Owns every change to a market's plan / subscription state and keeps
 * markets.plan_id / subscription_status / subscription_ends_at in sync.
 */
class SubscriptionService
{
    public function __construct(protected SmsCreditService $credits = new SmsCreditService()) {}

    /**
     * Put a market on a free trial of the given plan.
     */
    public function startTrial(Market $market, Plan $plan, ?int $createdBy = null): Subscription
    {
        return DB::transaction(function () use ($market, $plan, $createdBy) {
            $starts = today();
            $ends = $starts->copy()->addDays(max(1, $plan->trial_days));

            $subscription = $market->subscriptions()->create([
                'plan_id' => $plan->id,
                'status' => Subscription::STATUS_TRIAL,
                'billing_cycle' => Subscription::CYCLE_TRIAL,
                'starts_at' => $starts,
                'ends_at' => $ends,
                'amount_paid' => 0,
                'created_by' => $createdBy,
            ]);

            $this->sync($market, $subscription);

            if ($plan->trial_sms_credits > 0) {
                $this->credits->credit($market, $plan->trial_sms_credits, SmsCreditTransaction::TYPE_SUBSCRIPTION, [
                    'reference' => $plan->name . ' trial',
                    'note' => 'Trial SMS credits',
                    'created_by' => $createdBy,
                ]);
            }

            return $subscription;
        });
    }

    /**
     * Activate or renew a paid subscription. A renewal on a still-current
     * subscription extends from its end date; otherwise it starts today.
     */
    public function activate(Market $market, Plan $plan, string $cycle, array $payment = [], ?int $createdBy = null): Subscription
    {
        $cycle = $cycle === Subscription::CYCLE_YEARLY && $plan->offersYearly()
            ? Subscription::CYCLE_YEARLY
            : Subscription::CYCLE_MONTHLY;

        return DB::transaction(function () use ($market, $plan, $cycle, $payment, $createdBy) {
            $current = $market->currentSubscription();

            // Paid renewals of the same plan stack on top of the current paid period.
            // Trials and plan changes start today.
            $starts = ($current && $current->status === Subscription::STATUS_ACTIVE && $current->plan_id === $plan->id)
                ? $current->ends_at->copy()->addDay()
                : today();

            $ends = $cycle === Subscription::CYCLE_YEARLY
                ? $starts->copy()->addYear()->subDay()
                : $starts->copy()->addMonth()->subDay();

            if ($current && $current->isCurrent() && $starts->isToday()) {
                $current->update(['status' => $current->status === Subscription::STATUS_TRIAL
                    ? Subscription::STATUS_EXPIRED
                    : Subscription::STATUS_CANCELLED]);
            }

            $subscription = $market->subscriptions()->create([
                'plan_id' => $plan->id,
                'status' => Subscription::STATUS_ACTIVE,
                'billing_cycle' => $cycle,
                'starts_at' => $starts,
                'ends_at' => $ends,
                'next_sms_allocation_at' => $cycle === Subscription::CYCLE_YEARLY ? $starts->copy()->addMonth() : null,
                'amount_paid' => $payment['amount_paid'] ?? $plan->priceFor($cycle),
                'payment_method' => $payment['payment_method'] ?? null,
                'payment_reference' => $payment['payment_reference'] ?? null,
                'notes' => $payment['notes'] ?? null,
                'created_by' => $createdBy,
            ]);

            // Only the period that is live right now is mirrored on the market row.
            if ($starts->isToday() || !$current || !$current->isCurrent()) {
                $this->sync($market, $subscription);
            } else {
                $market->forceFill(['subscription_ends_at' => $ends])->save();
            }

            if ($plan->sms_credits_per_month > 0) {
                $this->credits->credit($market, $plan->sms_credits_per_month, SmsCreditTransaction::TYPE_SUBSCRIPTION, [
                    'reference' => $plan->name . ' (' . $cycle . ')',
                    'note' => 'Monthly SMS allowance for ' . $starts->format('M Y'),
                    'created_by' => $createdBy,
                ]);
            }

            return $subscription;
        });
    }

    public function cancel(Market $market, ?string $reason = null): void
    {
        DB::transaction(function () use ($market, $reason) {
            $market->subscriptions()
                ->whereIn('status', [Subscription::STATUS_TRIAL, Subscription::STATUS_ACTIVE])
                ->update(['status' => Subscription::STATUS_CANCELLED, 'notes' => $reason]);

            $market->forceFill([
                'subscription_status' => Subscription::STATUS_CANCELLED,
                'subscription_ends_at' => today()->subDay(),
            ])->save();
        });
    }

    /**
     * Daily housekeeping: expire finished periods, promote queued renewals,
     * and hand out monthly SMS allowances on yearly plans.
     *
     * @return array{expired:int, promoted:int, allocated:int}
     */
    public function processDaily(): array
    {
        $result = ['expired' => 0, 'promoted' => 0, 'allocated' => 0];
        $today = today();

        // 1. Expire trial/active subscriptions whose period has ended.
        $ended = Subscription::whereIn('status', [Subscription::STATUS_TRIAL, Subscription::STATUS_ACTIVE])
            ->whereDate('ends_at', '<', $today)
            ->with('market')
            ->get();

        foreach ($ended as $subscription) {
            $subscription->update(['status' => Subscription::STATUS_EXPIRED]);
            $result['expired']++;

            $market = $subscription->market;
            if (!$market) {
                continue;
            }

            // 2. A pre-paid renewal queued behind it becomes the live period.
            $next = $market->subscriptions()
                ->where('status', Subscription::STATUS_ACTIVE)
                ->whereDate('starts_at', '<=', $today)
                ->whereDate('ends_at', '>=', $today)
                ->orderBy('starts_at')
                ->first();

            if ($next) {
                $this->sync($market, $next);
                $result['promoted']++;
            } elseif ($market->subscription_status !== Subscription::STATUS_EXPIRED) {
                $market->forceFill(['subscription_status' => Subscription::STATUS_EXPIRED])->save();
            }
        }

        // 3. Monthly SMS allowance for yearly subscriptions.
        $due = Subscription::where('status', Subscription::STATUS_ACTIVE)
            ->whereNotNull('next_sms_allocation_at')
            ->whereDate('next_sms_allocation_at', '<=', $today)
            ->with(['market', 'plan'])
            ->get();

        foreach ($due as $subscription) {
            $next = $subscription->next_sms_allocation_at->copy();

            // Catch up any missed months (e.g. scheduler was down) without double-paying.
            while ($next->lte($today) && $next->lte($subscription->ends_at)) {
                if ($subscription->plan->sms_credits_per_month > 0 && $subscription->market) {
                    $this->credits->credit($subscription->market, $subscription->plan->sms_credits_per_month, SmsCreditTransaction::TYPE_SUBSCRIPTION, [
                        'reference' => $subscription->plan->name . ' (yearly)',
                        'note' => 'Monthly SMS allowance for ' . $next->format('M Y'),
                        'created_by' => null,
                    ]);
                    $result['allocated']++;
                }
                $next->addMonth();
            }

            $subscription->update([
                'next_sms_allocation_at' => $next->lte($subscription->ends_at) ? $next : null,
            ]);
        }

        return $result;
    }

    protected function sync(Market $market, Subscription $subscription): void
    {
        $market->forceFill([
            'plan_id' => $subscription->plan_id,
            'subscription_status' => $subscription->status,
            'subscription_ends_at' => $subscription->ends_at,
        ])->save();
    }
}
