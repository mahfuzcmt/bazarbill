<?php

namespace Tests\Feature;

use App\Exceptions\InsufficientSmsCreditsException;
use App\Models\Market;
use App\Models\SmsCreditTransaction;
use App\Models\SmsLog;
use App\Models\User;
use App\Services\SmsCreditService;
use App\Services\SmsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SmsCreditTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['super_admin', 'market_owner'] as $role) {
            Role::findOrCreate($role);
        }

        config(['services.sms.api_key' => 'platform-key']);
    }

    protected function superAdmin(): User
    {
        $user = User::factory()->create(['role' => 'super_admin', 'is_active' => true]);
        $user->assignRole('super_admin');

        return $user;
    }

    protected function marketOwner(Market $market): User
    {
        $user = User::factory()->create(['role' => 'market_owner', 'market_id' => $market->id, 'is_active' => true]);
        $user->assignRole('market_owner');

        return $user;
    }

    /* ---------------------------------------------------------------- service */

    public function test_credit_service_records_ledger_and_updates_balance(): void
    {
        $market = Market::factory()->create();
        $service = new SmsCreditService();

        $service->credit($market, 500, SmsCreditTransaction::TYPE_SUBSCRIPTION, ['reference' => 'Starter plan']);
        $service->credit($market, 200, SmsCreditTransaction::TYPE_RECHARGE, ['reference' => 'bKash TRX123']);
        $service->debit($market, 50, SmsCreditTransaction::TYPE_ADJUSTMENT, ['note' => 'correction']);

        $this->assertSame(650, $market->fresh()->sms_credits);

        $ledger = $market->smsCreditTransactions()->orderBy('id')->get();
        $this->assertCount(3, $ledger);
        $this->assertSame([500, 700, 650], $ledger->pluck('balance_after')->all());
        $this->assertSame([500, 200, -50], $ledger->pluck('amount')->all());
        $this->assertSame('bKash TRX123', $ledger[1]->reference);
    }

    public function test_credit_service_refuses_to_overdraw(): void
    {
        $market = Market::factory()->withCredits(10)->create();

        $this->expectException(InsufficientSmsCreditsException::class);
        (new SmsCreditService())->debit($market, 11, SmsCreditTransaction::TYPE_USAGE);
    }

    public function test_segment_calculation_matches_gateway_billing(): void
    {
        $this->assertSame(1, SmsService::calculateSegments(str_repeat('ক', 70)));
        $this->assertSame(2, SmsService::calculateSegments(str_repeat('ক', 71)));
        $this->assertSame(2, SmsService::calculateSegments(str_repeat('ক', 134)));
        $this->assertSame(3, SmsService::calculateSegments(str_repeat('ক', 135)));
        $this->assertSame(1, SmsService::calculateSegments(str_repeat('a', 160), false));
        $this->assertSame(2, SmsService::calculateSegments(str_repeat('a', 161), false));
    }

    /* --------------------------------------------------------- send + credits */

    public function test_platform_send_deducts_one_credit_per_segment(): void
    {
        Http::fake(['bulksmsbd.net/*' => Http::response(['response_code' => 202], 200)]);
        $market = Market::factory()->withCredits(10)->create();

        $sent = (new SmsService($market))->send('01711111111', str_repeat('ক', 100));

        $this->assertTrue($sent);
        $this->assertSame(8, $market->fresh()->sms_credits);

        $log = SmsLog::withoutGlobalScopes()->first();
        $this->assertSame('sent', $log->status);
        $this->assertSame('platform', $log->gateway);
        $this->assertSame(2, $log->credits_used);

        $usage = SmsCreditTransaction::where('type', 'usage')->first();
        $this->assertSame(-2, $usage->amount);
        $this->assertSame($log->id, $usage->sms_log_id);

        Http::assertSent(fn ($request) => $request['api_key'] === 'platform-key');
    }

    public function test_platform_send_is_blocked_when_balance_is_empty(): void
    {
        Http::fake();
        $market = Market::factory()->withCredits(0)->create();
        $service = new SmsService($market);

        $sent = $service->send('01711111111', 'test');

        $this->assertFalse($sent);
        $this->assertTrue($service->lastFailedForCredits);
        Http::assertNothingSent();

        $log = SmsLog::withoutGlobalScopes()->first();
        $this->assertSame('failed', $log->status);
        $this->assertSame(0, $log->credits_used);
        $this->assertSame(0, SmsCreditTransaction::count());
    }

    public function test_failed_gateway_send_refunds_credits(): void
    {
        Http::fake(['bulksmsbd.net/*' => Http::response(['response_code' => 1002, 'error_message' => 'Invalid number'], 200)]);
        $market = Market::factory()->withCredits(5)->create();
        $service = new SmsService($market);

        $this->assertFalse($service->send('01711111111', 'test'));
        $this->assertSame(5, $market->fresh()->sms_credits);
        $this->assertSame('Invalid number', $service->lastError);

        $types = SmsCreditTransaction::orderBy('id')->pluck('type')->all();
        $this->assertSame(['usage', 'refund'], $types);
        $this->assertSame(0, SmsLog::withoutGlobalScopes()->first()->credits_used);
    }

    public function test_own_gateway_send_does_not_touch_credits(): void
    {
        Http::fake(['bulksmsbd.net/*' => Http::response(['response_code' => 202], 200)]);
        $market = Market::factory()->withOwnGateway()->withCredits(3)->create();

        $this->assertTrue((new SmsService($market))->send('01711111111', 'test'));
        $this->assertSame(3, $market->fresh()->sms_credits);
        $this->assertSame(0, SmsCreditTransaction::count());
        $this->assertSame('own', SmsLog::withoutGlobalScopes()->first()->gateway);
        Http::assertSent(fn ($request) => $request['api_key'] === 'own-key');
    }

    public function test_platform_send_fails_cleanly_without_platform_key(): void
    {
        config(['services.sms.api_key' => null]);
        Http::fake();
        $market = Market::factory()->withCredits(5)->create();
        $service = new SmsService($market);

        $this->assertFalse($service->isConfigured());
        $this->assertFalse($service->send('01711111111', 'test'));
        $this->assertSame(5, $market->fresh()->sms_credits);
        Http::assertNothingSent();
    }

    /* ------------------------------------------------------------ admin UI */

    public function test_super_admin_can_add_credits(): void
    {
        $market = Market::factory()->create();

        $response = $this->actingAs($this->superAdmin())
            ->post(route('admin.markets.sms-credits.store', $market), [
                'operation' => 'add',
                'type' => 'recharge',
                'amount' => 300,
                'reference' => 'bKash 7X1ABC',
                'note' => 'Offline recharge',
            ]);

        $response->assertRedirect(route('admin.markets.sms-credits', $market));
        $this->assertSame(300, $market->fresh()->sms_credits);
        $this->assertDatabaseHas('sms_credit_transactions', [
            'market_id' => $market->id,
            'type' => 'recharge',
            'amount' => 300,
            'reference' => 'bKash 7X1ABC',
        ]);
    }

    public function test_super_admin_cannot_deduct_more_than_balance(): void
    {
        $market = Market::factory()->withCredits(20)->create();

        $response = $this->actingAs($this->superAdmin())
            ->from(route('admin.markets.sms-credits', $market))
            ->post(route('admin.markets.sms-credits.store', $market), [
                'operation' => 'deduct',
                'type' => 'adjustment',
                'amount' => 50,
            ]);

        $response->assertRedirect(route('admin.markets.sms-credits', $market));
        $response->assertSessionHas('error');
        $this->assertSame(20, $market->fresh()->sms_credits);
    }

    public function test_admin_pages_render(): void
    {
        $market = Market::factory()->withCredits(120)->create();
        (new SmsCreditService())->credit($market, 30, 'recharge', ['reference' => 'REF-1']);

        $admin = $this->superAdmin();

        $this->actingAs($admin)->get(route('admin.sms-credits.index'))
            ->assertOk()->assertSee($market->name)->assertSee('150');

        $this->actingAs($admin)->get(route('admin.markets.sms-credits', $market))
            ->assertOk()->assertSee('REF-1')->assertSee('150');
    }

    public function test_market_owner_cannot_manage_credits_but_can_view_history(): void
    {
        $market = Market::factory()->withCredits(40)->create();
        $owner = $this->marketOwner($market);

        $this->actingAs($owner)
            ->post(route('admin.markets.sms-credits.store', $market), ['operation' => 'add', 'type' => 'recharge', 'amount' => 10])
            ->assertForbidden();

        $this->actingAs($owner)->get(route('market-owner.settings.sms-credits'))
            ->assertOk()->assertSee('40');
    }
}
