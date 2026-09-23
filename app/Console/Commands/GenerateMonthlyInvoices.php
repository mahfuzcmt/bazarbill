<?php

namespace App\Console\Commands;

use App\Models\Market;
use App\Services\InvoiceGenerationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class GenerateMonthlyInvoices extends Command
{
    protected $signature = 'invoices:generate-monthly {--month= : Billing month (YYYY-MM), defaults to the current month}';

    protected $description = 'Create rent invoices for every market that has automatic monthly invoicing enabled';

    public function handle(InvoiceGenerationService $generator): int
    {
        $month = $this->option('month') ?: now()->format('Y-m');

        if (!preg_match('/^\d{4}-\d{2}$/', $month)) {
            $this->error('Month must be in YYYY-MM format.');
            return self::FAILURE;
        }

        $markets = Market::where('status', 'active')->get()
            ->filter(fn (Market $m) => $m->getSetting('auto_generate', false) && $m->hasActiveSubscription());

        $totalCreated = 0;

        foreach ($markets as $market) {
            $dueDays = (int) $market->getSetting('due_days', 7);
            $dueDate = now()->startOfMonth()->addDays(max(1, $dueDays));

            $result = $generator->generateForMarket(
                $market,
                $month,
                $dueDate,
                true,
                (bool) $market->getSetting('sms_on_invoice', false),
            );

            $totalCreated += $result['created'];

            $this->line(sprintf(
                '%s: %d created, %d skipped, SMS %d sent / %d failed%s',
                $market->name,
                $result['created'],
                $result['skipped'],
                $result['sms_sent'],
                $result['sms_failed'],
                $result['sms_blocked_for_credits'] ? ' (out of credits)' : '',
            ));

            if ($result['sms_blocked_for_credits']) {
                Log::warning('Monthly invoice SMS blocked: market out of SMS credits', ['market_id' => $market->id]);
            }
        }

        $this->info("Generated {$totalCreated} invoices for {$markets->count()} markets ({$month}).");

        return self::SUCCESS;
    }
}
