<?php

namespace App\Console\Commands;

use App\Services\SubscriptionService;
use Illuminate\Console\Command;

class ProcessSubscriptions extends Command
{
    protected $signature = 'subscriptions:process';

    protected $description = 'Expire finished subscriptions, promote queued renewals and allocate monthly SMS credits on yearly plans';

    public function handle(SubscriptionService $subscriptions): int
    {
        $result = $subscriptions->processDaily();

        $this->info(sprintf(
            'Expired: %d, promoted renewals: %d, SMS allocations: %d',
            $result['expired'],
            $result['promoted'],
            $result['allocated'],
        ));

        return self::SUCCESS;
    }
}
