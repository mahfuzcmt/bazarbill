<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\Market;
use App\Models\Shop;
use App\Models\SmsLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class InvoiceAutomationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.sms.api_key' => 'platform-key']);
    }

    protected function marketWithShop(array $settings, int $credits = 10): array
    {
        $market = Market::factory()->withCredits($credits)->create(['settings' => $settings]);
        $owner = User::factory()->create(['role' => 'shop_owner', 'market_id' => $market->id, 'phone' => '01711111111']);
        $shop = Shop::create([
            'market_id' => $market->id, 'shop_owner_id' => $owner->id, 'shop_number' => 'B-1',
            'rent_amount' => 5000, 'shop_type' => 'general', 'status' => 'active',
        ]);

        return [$market, $shop];
    }

    public function test_monthly_generation_only_runs_for_opted_in_markets(): void
    {
        Http::fake(['bulksmsbd.net/*' => Http::response(['response_code' => 202], 200)]);
        [$auto, $autoShop] = $this->marketWithShop(['auto_generate' => true, 'sms_on_invoice' => true, 'due_days' => 10]);
        [$manual, $manualShop] = $this->marketWithShop(['auto_generate' => false]);

        $this->artisan('invoices:generate-monthly')->assertSuccessful();

        $invoice = Invoice::withoutGlobalScopes()->where('shop_id', $autoShop->id)->first();
        $this->assertNotNull($invoice);
        $this->assertSame(now()->format('Y-m'), $invoice->billing_month);
        $this->assertSame(5000.0, (float) $invoice->total_amount);
        $this->assertTrue($invoice->due_date->isSameDay(now()->startOfMonth()->addDays(10)));

        $this->assertSame(0, Invoice::withoutGlobalScopes()->where('shop_id', $manualShop->id)->count());
        $log = SmsLog::withoutGlobalScopes()->where('status', 'sent')->sole();
        $this->assertGreaterThanOrEqual(1, $log->credits_used);
        $this->assertSame(10 - $log->credits_used, $auto->fresh()->sms_credits);

        // Running again does not duplicate.
        $this->artisan('invoices:generate-monthly')->assertSuccessful();
        $this->assertSame(1, Invoice::withoutGlobalScopes()->where('shop_id', $autoShop->id)->count());
    }

    public function test_monthly_generation_carries_forward_previous_due(): void
    {
        [$market, $shop] = $this->marketWithShop(['auto_generate' => true]);
        Invoice::create([
            'market_id' => $market->id, 'shop_id' => $shop->id, 'billing_month' => now()->subMonth()->format('Y-m'),
            'rent_amount' => 5000, 'total_amount' => 5000, 'paid_amount' => 2000, 'due_amount' => 3000,
            'status' => 'partial', 'due_date' => now()->subMonth()->toDateString(),
        ]);

        $this->artisan('invoices:generate-monthly')->assertSuccessful();

        $new = Invoice::withoutGlobalScopes()->where('shop_id', $shop->id)->where('billing_month', now()->format('Y-m'))->first();
        $this->assertSame(3000.0, (float) $new->previous_due);
        $this->assertSame(8000.0, (float) $new->total_amount);
    }

    public function test_overdue_marking(): void
    {
        [$market, $shop] = $this->marketWithShop([]);
        $late = Invoice::create([
            'market_id' => $market->id, 'shop_id' => $shop->id, 'billing_month' => '2026-07',
            'rent_amount' => 100, 'total_amount' => 100, 'paid_amount' => 0, 'due_amount' => 100,
            'status' => 'pending', 'due_date' => today()->subDay()->toDateString(),
        ]);
        $onTime = Invoice::create([
            'market_id' => $market->id, 'shop_id' => $shop->id, 'billing_month' => '2026-08',
            'rent_amount' => 100, 'total_amount' => 100, 'paid_amount' => 0, 'due_amount' => 100,
            'status' => 'pending', 'due_date' => today()->addDay()->toDateString(),
        ]);

        $this->artisan('invoices:mark-overdue')->assertSuccessful();

        $this->assertSame('overdue', $late->fresh()->status);
        $this->assertSame('pending', $onTime->fresh()->status);
    }

    public function test_reminders_are_sent_once_per_window_and_only_when_enabled(): void
    {
        Http::fake(['bulksmsbd.net/*' => Http::response(['response_code' => 202], 200)]);
        [$market, $shop] = $this->marketWithShop(['auto_reminder' => true, 'reminder_days_before' => 3, 'reminder_repeat_days' => 7]);
        [$quiet, $quietShop] = $this->marketWithShop(['auto_reminder' => false]);

        foreach ([[$market, $shop], [$quiet, $quietShop]] as [$m, $s]) {
            Invoice::create([
                'market_id' => $m->id, 'shop_id' => $s->id, 'billing_month' => '2026-09',
                'rent_amount' => 100, 'total_amount' => 100, 'paid_amount' => 0, 'due_amount' => 100,
                'status' => 'pending', 'due_date' => today()->addDays(2)->toDateString(),
            ]);
        }

        $this->artisan('invoices:send-reminders')->assertSuccessful();

        $reminded = Invoice::withoutGlobalScopes()->where('market_id', $market->id)->first();
        $this->assertNotNull($reminded->last_reminder_at);
        $this->assertSame(1, $reminded->reminder_count);
        $this->assertSame(1, SmsLog::withoutGlobalScopes()->count());
        $this->assertNull(Invoice::withoutGlobalScopes()->where('market_id', $quiet->id)->first()->last_reminder_at);

        // Next day: inside the repeat window, so no second SMS.
        $this->travel(1)->days();
        $this->artisan('invoices:send-reminders')->assertSuccessful();
        $this->assertSame(1, SmsLog::withoutGlobalScopes()->count());

        // After the repeat window (invoice is now overdue) it is reminded again.
        $this->travel(7)->days();
        $this->artisan('invoices:send-reminders')->assertSuccessful();
        $this->assertSame(2, SmsLog::withoutGlobalScopes()->count());
        $this->assertSame(2, $reminded->fresh()->reminder_count);
    }

    public function test_reminders_stop_when_credits_run_out(): void
    {
        Http::fake(['bulksmsbd.net/*' => Http::response(['response_code' => 202], 200)]);
        [$market, $shop] = $this->marketWithShop(['auto_reminder' => true], credits: 0);
        Invoice::create([
            'market_id' => $market->id, 'shop_id' => $shop->id, 'billing_month' => '2026-09',
            'rent_amount' => 100, 'total_amount' => 100, 'paid_amount' => 0, 'due_amount' => 100,
            'status' => 'overdue', 'due_date' => today()->subDays(5)->toDateString(),
        ]);

        $this->artisan('invoices:send-reminders')->assertSuccessful();

        Http::assertNothingSent();
        $this->assertNull(Invoice::withoutGlobalScopes()->first()->last_reminder_at);
    }
}
