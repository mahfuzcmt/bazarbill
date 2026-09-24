<?php

namespace Tests\Feature;

use App\Models\Market;
use App\Models\Setting;
use App\Models\SmsLog;
use App\Models\User;
use App\Services\SmsService;
use App\Support\SmsTemplates;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SmsTemplateTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.sms.api_key' => 'k']);
        Http::fake(['bulksmsbd.net/*' => Http::response(['response_code' => 202], 200)]);
    }

    public function test_template_chain_market_over_platform_over_builtin(): void
    {
        $market = Market::factory()->withCredits(50)->create(['name' => 'Test Bazar']);
        $data = ['phone' => '01711111111', 'shop_owner' => 'Karim', 'month' => 'Oct 2026', 'amount' => '8,500', 'invoice_no' => 'INV-1', 'shop_no' => '27', 'due_date' => '10 Oct 2026'];

        // built-in
        (new SmsService($market))->sendInvoiceNotification($data);
        $this->assertStringContainsString('Test Bazar', SmsLog::withoutGlobalScopes()->latest('id')->first()->message);
        $this->assertStringContainsString('INV-1', SmsLog::withoutGlobalScopes()->latest('id')->first()->message);

        // platform default set by super admin
        Setting::set('sms.template.invoice_generated', 'Platform: {shop_owner} owes {amount} for {month} ({market})');
        (new SmsService($market))->sendInvoiceNotification($data);
        $this->assertSame('Platform: Karim owes 8,500 for Oct 2026 (Test Bazar)', SmsLog::withoutGlobalScopes()->latest('id')->first()->message);

        // market's own text wins
        $market->update(['sms_templates' => ['invoice_generated' => 'Own: shop {shop_no} bill {invoice_no} due {due_date}']]);
        (new SmsService($market->fresh()))->sendInvoiceNotification($data);
        $this->assertSame('Own: shop 27 bill INV-1 due 10 Oct 2026', SmsLog::withoutGlobalScopes()->latest('id')->first()->message);
    }

    public function test_market_owner_can_edit_and_clear_templates(): void
    {
        Role::findOrCreate('market_owner');
        $market = Market::factory()->create();
        $owner = User::factory()->create(['role' => 'market_owner', 'market_id' => $market->id, 'is_active' => true, 'language_preference' => 'en']);
        $owner->assignRole('market_owner');

        $this->actingAs($owner)->get(route('market-owner.settings.index'))->assertOk()->assertSee('SMS message texts')->assertSee('{invoice_no}');

        $this->actingAs($owner)->put(route('market-owner.settings.sms'), [
            'sms_sender_id' => '', 'templates' => ['payment_reminder' => 'Pay {amount} for shop {shop_no}', 'invoice_generated' => '   '],
        ])->assertSessionHasNoErrors();

        $this->assertSame(['payment_reminder' => 'Pay {amount} for shop {shop_no}'], $market->fresh()->sms_templates);
        $this->assertSame('Pay {amount} for shop {shop_no}', $market->fresh()->resolveSmsTemplate('payment_reminder'));
        $this->assertSame(SmsTemplates::builtIn()['invoice_generated'], $market->fresh()->resolveSmsTemplate('invoice_generated'));

        $this->actingAs($owner)->put(route('market-owner.settings.sms'), ['templates' => ['payment_reminder' => str_repeat('x', 301)]])
            ->assertSessionHasErrors('templates.payment_reminder');
    }

    public function test_super_admin_sets_platform_defaults(): void
    {
        Role::findOrCreate('super_admin');
        $admin = User::factory()->create(['role' => 'super_admin', 'is_active' => true, 'language_preference' => 'en']);
        $admin->assignRole('super_admin');

        $this->actingAs($admin)->get(route('admin.sms-settings.index'))->assertOk()->assertSee('Default SMS templates');

        $this->actingAs($admin)->put(route('admin.sms-settings.update'), [
            'sender_id' => '880', 'low_credit_threshold' => 20,
            'templates' => ['payment_received' => 'Got {amount}, receipt {receipt_no}. Remaining {due_amount}.', 'payment_reminder' => ''],
        ])->assertSessionHasNoErrors();

        $this->assertSame('Got {amount}, receipt {receipt_no}. Remaining {due_amount}.', SmsTemplates::platformDefault('payment_received'));
        $this->assertSame(SmsTemplates::builtIn()['payment_reminder'], SmsTemplates::platformDefault('payment_reminder'));
    }
}
