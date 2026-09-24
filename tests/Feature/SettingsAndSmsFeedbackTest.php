<?php

namespace Tests\Feature;

use App\Models\Market;
use App\Models\Shop;
use App\Models\SmsLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SettingsAndSmsFeedbackTest extends TestCase
{
    use RefreshDatabase;

    protected function owner(Market $market): User
    {
        Role::findOrCreate('market_owner');
        $u = User::factory()->create(['role' => 'market_owner', 'market_id' => $market->id, 'is_active' => true, 'language_preference' => 'en']);
        $u->assignRole('market_owner');

        return $u;
    }

    public function test_market_settings_save_contact_and_logo_and_logo_is_served(): void
    {
        Storage::fake('public');
        $market = Market::factory()->create();

        $this->actingAs($this->owner($market))->put(route('market-owner.settings.update'), [
            'name' => 'Updated Market', 'contact_phone' => '01799998888', 'contact_email' => 'hello@market.test',
            'logo' => UploadedFile::fake()->image('logo.png', 300, 300),
        ])->assertSessionHasNoErrors();

        $market->refresh();
        $this->assertSame('01799998888', $market->phone);
        $this->assertSame('hello@market.test', $market->email);
        $this->assertNotNull($market->logo);
        Storage::disk('public')->assertExists($market->logo);

        // Fallback route serves the file even without the public/storage symlink.
        $this->get('/storage/' . $market->logo)->assertOk();
        $this->get('/storage/../.env')->assertNotFound();
    }

    public function test_bulk_invoice_sms_failure_shows_the_gateway_reason(): void
    {
        config(['services.sms.api_key' => null]); // platform key missing
        Http::fake();
        $market = Market::factory()->withCredits(10)->create();
        $owner = $this->owner($market);
        $tenant = User::factory()->create(['role' => 'shop_owner', 'market_id' => $market->id, 'phone' => '01711111111']);
        Shop::withoutGlobalScopes()->create(['market_id' => $market->id, 'shop_owner_id' => $tenant->id, 'shop_number' => 'S-1', 'rent_amount' => 1000, 'shop_type' => 'general', 'status' => 'active']);

        $this->actingAs($owner)->post(route('market-owner.invoices.generate-bulk'), [
            'billing_month' => now()->format('Y-m'), 'due_date' => now()->addDays(7)->toDateString(), 'send_sms' => 1,
        ])->assertRedirect(route('market-owner.invoices.index'))
          ->assertSessionHas('error', fn ($m) => str_contains($m, 'gateway not configured'));

        // The attempt is visible in the SMS log with its reason.
        $log = SmsLog::withoutGlobalScopes()->first();
        $this->assertSame('failed', $log->status);
        $this->assertStringContainsString('not configured', $log->api_response);
    }
}
