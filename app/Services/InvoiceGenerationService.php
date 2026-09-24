<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Market;
use App\Models\Shop;
use Carbon\Carbon;

/**
 * Creates one rent invoice per active shop for a billing month.
 * Used by the market owner's bulk-generate button and the monthly scheduler.
 */
class InvoiceGenerationService
{
    /**
     * @return array{created:int, skipped:int, sms_sent:int, sms_failed:int, sms_blocked_for_credits:bool}
     */
    public function generateForMarket(
        Market $market,
        string $billingMonth,
        Carbon $dueDate,
        bool $includePreviousDue = true,
        bool $sendSms = false,
    ): array {
        $result = ['created' => 0, 'skipped' => 0, 'sms_sent' => 0, 'sms_failed' => 0, 'sms_blocked_for_credits' => false, 'sms_last_error' => null];

        $shops = Shop::withoutGlobalScopes()
            ->where('market_id', $market->id)
            ->where('status', 'active')
            ->with('shopOwner')
            ->get();

        $smsService = $sendSms ? new SmsService($market) : null;

        foreach ($shops as $shop) {
            $exists = Invoice::withoutGlobalScopes()
                ->where('shop_id', $shop->id)
                ->where('billing_month', $billingMonth)
                ->exists();

            if ($exists) {
                $result['skipped']++;
                continue;
            }

            $previousDue = $includePreviousDue ? $this->outstandingDueForShop($shop->id) : 0;
            $totalAmount = $shop->rent_amount + $previousDue;

            $invoice = Invoice::create([
                'market_id' => $market->id,
                'shop_id' => $shop->id,
                'billing_month' => $billingMonth,
                'rent_amount' => $shop->rent_amount,
                'previous_due' => $previousDue,
                'discount' => 0,
                'late_fee' => 0,
                'total_amount' => $totalAmount,
                'paid_amount' => 0,
                'due_amount' => $totalAmount,
                'status' => 'pending',
                'due_date' => $dueDate->toDateString(),
            ]);

            $result['created']++;

            if ($smsService && $shop->shopOwner?->phone) {
                $sent = $smsService->sendInvoiceNotification([
                    'phone' => $shop->shopOwner->phone,
                    'shop_owner' => $shop->shopOwner->getLocalizedName(),
                    'month' => $invoice->getBillingMonthFormattedBn(),
                    'amount' => number_format($invoice->total_amount),
                    'invoice_no' => $invoice->invoice_number,
                ]);

                if ($sent) {
                    $result['sms_sent']++;
                } else {
                    $result['sms_failed']++;
                    $result['sms_last_error'] = $smsService->lastError;
                    if ($smsService->lastFailedForCredits) {
                        $result['sms_blocked_for_credits'] = true;
                    }
                }
            }
        }

        return $result;
    }

    /**
     * Sum of unpaid balances on the shop's existing invoices.
     */
    public function outstandingDueForShop(int $shopId): float
    {
        return (float) Invoice::withoutGlobalScopes()
            ->where('shop_id', $shopId)
            ->where('status', '!=', 'paid')
            ->sum('due_amount');
    }
}
