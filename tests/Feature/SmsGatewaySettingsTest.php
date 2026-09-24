<?php

namespace Tests\Feature;

use App\Models\Market;
use App\Models\Setting;
use App\Models\SmsLog;
use App\Models\User;
use App\Services\SmsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SmsGatewaySettingsTest extends TestCase
{
    use RefreshDatabase;

    protected function admin(): User
    {
        Role::findOrCreate('super_admin');
        $u = User::factory()->create(['role' => 'super_admin', 'is_active' => true, 'language_preference' => 'en']);
        $u->assignRole('super_admin');

        return $u;
    }

    public function test_admin_can_save_platform_credentials_and_they_are_used_for_sending(): void
    {
        config(['services.sms.api_key' => null]);
        Http::fake(['bulksmsbd.net/*' => Http::response(['response_code' => 202], 200)]);

        $this->actingAs($this->admin())->put(route('admin.sms-settings.update'), [
            'api_key' => 'db-key-123',
            'sender_id' => '8809600000001',
            'low_credit_threshold' => 50,
        ])->assertRedirect(route('admin.sms-settings.index'));

        $this->assertSame('db-key-123', Setting::smsApiKey());
        $this->assertSame(50, Setting::smsLowCreditThreshold());

        $market = Market::factory()->withCredits(5)->create();
        $this->assertTrue((new SmsService($market))->send('01711111111', 'hello'));
        Http::assertSent(fn ($r) => $r['api_key'] === 'db-key-123' && $r['senderid'] === '8809600000001');
        $this->assertSame(4, $market->fresh()->sms_credits);
    }

    public function test_blank_key_keeps_existing_and_clear_removes_it(): void
    {
        Setting::set('sms.api_key', 'keep-me');
        $admin = $this->admin();

        $this->actingAs($admin)->put(route('admin.sms-settings.update'), ['api_key' => '', 'sender_id' => '880', 'low_credit_threshold' => 20]);
        $this->assertSame('keep-me', Setting::smsApiKey());

        $this->actingAs($admin)->put(route('admin.sms-settings.update'), ['clear_api_key' => 1, 'sender_id' => '880', 'low_credit_threshold' => 20]);
        config(['services.sms.api_key' => null]);
        $this->assertNull(Setting::smsApiKey());
    }

    public function test_test_sms_reports_gateway_error(): void
    {
        Setting::set('sms.api_key', 'k');
        Http::fake(['bulksmsbd.net/*' => Http::response(['response_code' => 1007, 'error_message' => 'Insufficient balance'], 200)]);

        $this->actingAs($this->admin())->from(route('admin.sms-settings.index'))
            ->post(route('admin.sms-settings.test'), ['phone' => '01712345678'])
            ->assertRedirect(route('admin.sms-settings.index'))
            ->assertSessionHas('error', fn ($m) => str_contains($m, 'Insufficient balance'));

        $this->assertSame(0, SmsLog::withoutGlobalScopes()->count()); // test sends are not logged against a market
    }

    public function test_pages_render_with_logs(): void
    {
        Http::fake(['bulksmsbd.net/api/getBalanceApi*' => Http::response(['balance' => 123.5], 200)]);
        Setting::set('sms.api_key', 'k');
        $market = Market::factory()->create(['name' => 'Log Market']);
        SmsLog::create(['market_id' => $market->id, 'recipient_phone' => '8801711111111', 'message' => 'hi', 'status' => 'failed', 'gateway' => 'platform', 'api_response' => 'Invalid number']);

        $admin = $this->admin();
        $this->actingAs($admin)->get(route('admin.sms-settings.index'))->assertOk()->assertSee('123.50')->assertSee('Invalid number');
        $this->actingAs($admin)->get(route('admin.sms-logs.index', ['status' => 'failed']))->assertOk()->assertSee('8801711111111');
        $this->actingAs($admin)->get(route('admin.sms-logs.index', ['status' => 'sent']))->assertOk()->assertDontSee('8801711111111');
    }
}
