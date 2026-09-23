<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use App\Models\Market;
use App\Services\SmsService;
use Illuminate\Console\Command;

/**
 * Sends a payment-reminder SMS for unpaid invoices in markets that opted in.
 *
 * Rules per market (settings): reminder_days_before (default 3) sends one
 * reminder shortly before the due date; overdue invoices are reminded again
 * every reminder_repeat_days (default 7). An invoice is never reminded twice
 * within reminder_repeat_days.
 */
class SendInvoiceReminders extends Command
{
    protected $signature = 'invoices:send-reminders';

    protected $description = 'Send SMS payment reminders for due and overdue invoices where auto reminders are enabled';

    public function handle(): int
    {
        $markets = Market::where('status', 'active')->get()
            ->filter(fn (Market $m) => $m->getSetting('auto_reminder', false) && $m->hasActiveSubscription());

        $sent = 0;
        $failed = 0;

        foreach ($markets as $market) {
            $daysBefore = (int) $market->getSetting('reminder_days_before', 3);
            $repeatDays = max(1, (int) $market->getSetting('reminder_repeat_days', 7));
            $smsService = new SmsService($market);

            $invoices = Invoice::withoutGlobalScopes()
                ->where('market_id', $market->id)
                ->whereIn('status', ['pending', 'partial', 'overdue'])
                ->where('due_amount', '>', 0)
                ->whereDate('due_date', '<=', today()->addDays($daysBefore))
                ->where(function ($q) use ($repeatDays) {
                    $q->whereNull('last_reminder_at')
                      ->orWhere('last_reminder_at', '<=', now()->subDays($repeatDays));
                })
                ->with('shop.shopOwner')
                ->get();

            foreach ($invoices as $invoice) {
                $owner = $invoice->shop?->shopOwner;
                if (!$owner?->phone) {
                    continue;
                }

                $ok = $smsService->sendPaymentReminder([
                    'phone' => $owner->phone,
                    'shop_owner' => $owner->getLocalizedName(),
                    'amount' => number_format($invoice->due_amount),
                ]);

                if ($ok) {
                    $invoice->forceFill([
                        'last_reminder_at' => now(),
                        'reminder_count' => $invoice->reminder_count + 1,
                    ])->save();
                    $sent++;
                } else {
                    $failed++;
                    if ($smsService->lastFailedForCredits) {
                        $this->warn("{$market->name}: out of SMS credits, skipping remaining reminders.");
                        break;
                    }
                }
            }
        }

        $this->info("Reminders sent: {$sent}, failed: {$failed}.");

        return self::SUCCESS;
    }
}
