<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Run `php artisan schedule:run` every minute from cron (see CPANEL_DEPLOYMENT.md).
Schedule::command('subscriptions:process')->dailyAt('00:10');
Schedule::command('invoices:mark-overdue')->dailyAt('00:20');
Schedule::command('invoices:generate-monthly')->monthlyOn(1, '06:00');
Schedule::command('invoices:send-reminders')->dailyAt('10:00');
