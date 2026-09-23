<?php

namespace App\Services;

use App\Exceptions\InsufficientSmsCreditsException;
use App\Models\Market;
use App\Models\SmsCreditTransaction;
use Illuminate\Support\Facades\DB;

/**
 * Single entry point for every change to a market's prepaid SMS balance.
 * Every change is applied under a row lock and recorded as a transaction,
 * so the ledger always reconciles with markets.sms_credits.
 */
class SmsCreditService
{
    /**
     * Add credits (subscription allocation, offline recharge, positive adjustment).
     */
    public function credit(Market $market, int $amount, string $type, array $meta = []): SmsCreditTransaction
    {
        if ($amount <= 0) {
            throw new \InvalidArgumentException('Credit amount must be positive.');
        }

        return $this->apply($market, $amount, $type, $meta);
    }

    /**
     * Remove credits (SMS usage, negative adjustment).
     *
     * @throws InsufficientSmsCreditsException when the balance would go negative
     */
    public function debit(Market $market, int $amount, string $type, array $meta = []): SmsCreditTransaction
    {
        if ($amount <= 0) {
            throw new \InvalidArgumentException('Debit amount must be positive.');
        }

        return $this->apply($market, -$amount, $type, $meta);
    }

    /**
     * Return credits that were reserved for an SMS the gateway failed to deliver.
     */
    public function refund(Market $market, int $amount, array $meta = []): SmsCreditTransaction
    {
        return $this->credit($market, $amount, SmsCreditTransaction::TYPE_REFUND, $meta);
    }

    public function hasCredits(Market $market, int $required = 1): bool
    {
        return $market->sms_credits >= $required;
    }

    protected function apply(Market $market, int $delta, string $type, array $meta): SmsCreditTransaction
    {
        return DB::transaction(function () use ($market, $delta, $type, $meta) {
            /** @var Market $locked */
            $locked = Market::query()->whereKey($market->getKey())->lockForUpdate()->firstOrFail();

            $newBalance = $locked->sms_credits + $delta;

            if ($newBalance < 0) {
                throw new InsufficientSmsCreditsException(abs($delta), $locked->sms_credits);
            }

            $locked->forceFill(['sms_credits' => $newBalance])->save();

            // Keep the caller's instance in sync without a second query.
            $market->sms_credits = $newBalance;

            return SmsCreditTransaction::create([
                'market_id' => $market->getKey(),
                'type' => $type,
                'amount' => $delta,
                'balance_after' => $newBalance,
                'reference' => $meta['reference'] ?? null,
                'note' => $meta['note'] ?? null,
                'created_by' => $meta['created_by'] ?? auth()->id(),
                'sms_log_id' => $meta['sms_log_id'] ?? null,
            ]);
        });
    }
}
