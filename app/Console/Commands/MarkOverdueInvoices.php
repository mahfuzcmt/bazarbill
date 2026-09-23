<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use Illuminate\Console\Command;

class MarkOverdueInvoices extends Command
{
    protected $signature = 'invoices:mark-overdue';

    protected $description = 'Flag unpaid invoices whose due date has passed as overdue';

    public function handle(): int
    {
        $count = Invoice::withoutGlobalScopes()
            ->whereIn('status', ['pending', 'partial'])
            ->whereDate('due_date', '<', today())
            ->update(['status' => 'overdue']);

        $this->info("Marked {$count} invoices as overdue.");

        return self::SUCCESS;
    }
}
