<?php

namespace Tests\Feature;

use App\Models\Market;
use App\Models\Plan;
use App\Models\Shop;
use App\Models\Subscription;
use App\Models\User;
use App\Services\SubscriptionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SubscriptionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['super_admin', 'market_owner', 'collector', 'shop_owner'] as $role) {
            Role::findOrCreate($role);
        }
    }

    protected function superAdmin(): User
    {
        $user = User::factory()->create(['role' => 'super_admin', 'is_active' => true]);
        $user->assignRole('super_admin');

        return $user;
    }

    protected function owner(Market $market): User
    {
        $user = User::factory()->create(['role' => 'market_owner', 'market_id' => $market->id, 'is_active' => true]);
        $user->assignRole('market_owner');

        return $user;
    }

    /* ------------------------------------------------------------ signup */

    public function test_self_service_signup_creates_market_owner_and_trial(): void
    {
        $plan = Plan::factory()->create(['is_default' => true, 'trial_days' => 10, 'trial_sms_credits' => 25]);

        $response = $this->post(route('register'), [
            'market_name' => 'Gulshan Kitchen Market',
            'market_name_bn' => 'গুলশান কিচেন মার্কেট',
            'name' => 'Karim Mia',
            'phone' => '01712345678',
            'email' => 'karim@example.com',
            'password' => 'Password!123',
            'password_confirmation' => 'Password!123',
        ]);

        $response->assertRedirect(route('dashboard', absolute: false));
        $this->assertAuthenticated();

        $user = User::where('email', 'karim@example.com')->firstOrFail();
        $this->assertSame('market_owner', $user->role);
        $this->assertTrue($user->hasRole('market_owner'));

        $market = $user->market;
        $this->assertSame('Gulshan Kitchen Market', $market->name);
        $this->assertSame($plan->id, $market->plan_id);
        $this->assertSame('trial', $market->subscription_status);
        $this->assertTrue($market->subscription_ends_at->isSameDay(today()->addDays(10)));
        $this->assertSame(25, $market->sms_credits);
        $this->assertDatabaseHas('subscriptions', ['market_id' => $market->id, 'status' => 'trial', 'billing_cycle' => 'trial']);
    }

    public function test_signup_rejects_invalid_bangladeshi_phone(): void
    {
        Plan::factory()->create(['is_default' => true]);

        $this->post(route('register'), [
            'market_name' => 'X', 'name' => 'Y', 'phone' => '12345',
            'email' => 'a@b.com', 'password' => 'Password!123', 'password_confirmation' => 'Password!123',
        ])->assertSessionHasErrors('phone');
    }

    /* ---------------------------------------------------------- enforcement */

    public function test_expired_market_users_are_sent_to_the_expired_page(): void
    {
        $plan = Plan::factory()->create();
        $market = Market::factory()->create([
            'plan_id' => $plan->id,
            'subscription_status' => 'expired',
            'subscription_ends_at' => today()->subDay(),
        ]);

        $this->actingAs($this->owner($market))
            ->get(route('market-owner.dashboard'))
            ->assertRedirect(route('subscription.expired'));

        $this->actingAs($this->owner($market))
            ->get(route('subscription.expired'))
            ->assertOk()
            ->assertSee($plan->name);
    }

    public function test_legacy_market_without_plan_is_not_blocked(): void
    {
        $market = Market::factory()->create();

        $this->actingAs($this->owner($market))
            ->get(route('market-owner.dashboard'))
            ->assertOk();
    }

    public function test_shop_limit_is_enforced(): void
    {
        $plan = Plan::factory()->create(['shop_limit' => 1]);
        $market = Market::factory()->create([
            'plan_id' => $plan->id,
            'subscription_status' => 'active',
            'subscription_ends_at' => today()->addMonth(),
        ]);
        $owner = $this->owner($market);
        Shop::create(['market_id' => $market->id, 'shop_number' => 'A-1', 'rent_amount' => 1000, 'shop_type' => 'general', 'status' => 'active']);

        $this->actingAs($owner)
            ->from(route('market-owner.shops.create'))
            ->post(route('market-owner.shops.store'), [
                'shop_number' => 'A-2', 'rent_amount' => 1000, 'shop_type' => 'general', 'status' => 'active',
            ])
            ->assertRedirect(route('market-owner.shops.create'))
            ->assertSessionHas('error');

        $this->assertSame(1, Shop::withoutGlobalScopes()->where('market_id', $market->id)->count());
    }

    /* ------------------------------------------------------------- service */

    public function test_activation_credits_sms_and_sets_period(): void
    {
        $plan = Plan::factory()->create(['sms_credits_per_month' => 300, 'monthly_price' => 2500]);
        $market = Market::factory()->create();

        $sub = (new SubscriptionService())->activate($market, $plan, 'monthly', ['payment_method' => 'bkash', 'payment_reference' => 'TRX1']);

        $this->assertSame('active', $sub->status);
        $this->assertTrue($sub->starts_at->isToday());
        $this->assertTrue($sub->ends_at->isSameDay(today()->addMonth()->subDay()));
        $this->assertSame(2500.0, (float) $sub->amount_paid);
        $this->assertSame(300, $market->fresh()->sms_credits);
        $this->assertSame('active', $market->fresh()->subscription_status);
    }

    public function test_renewal_of_same_plan_extends_from_current_end_date(): void
    {
        $plan = Plan::factory()->create(['sms_credits_per_month' => 100]);
        $market = Market::factory()->create();
        $service = new SubscriptionService();

        $first = $service->activate($market, $plan, 'monthly');
        $second = $service->activate($market, $plan, 'monthly');

        $this->assertTrue($second->starts_at->isSameDay($first->ends_at->copy()->addDay()));
        $this->assertTrue($market->fresh()->subscription_ends_at->isSameDay($second->ends_at));
        $this->assertSame('active', $first->fresh()->status); // still the live period
        $this->assertSame(200, $market->fresh()->sms_credits);
    }

    public function test_yearly_plan_gets_monthly_sms_allowance_via_daily_processing(): void
    {
        $plan = Plan::factory()->create(['sms_credits_per_month' => 100, 'yearly_price' => 10000]);
        $market = Market::factory()->create();
        $service = new SubscriptionService();

        $sub = $service->activate($market, $plan, 'yearly');
        $this->assertSame(100, $market->fresh()->sms_credits);
        $this->assertTrue($sub->next_sms_allocation_at->isSameDay(today()->addMonth()));

        // Nothing due yet.
        $this->assertSame(0, $service->processDaily()['allocated']);

        // Travel one month ahead: one allowance is granted and the pointer moves.
        $this->travel(1)->months();
        $this->assertSame(1, $service->processDaily()['allocated']);
        $this->assertSame(200, $market->fresh()->sms_credits);
        $this->assertSame(0, $service->processDaily()['allocated']);
    }

    public function test_daily_processing_expires_finished_subscriptions(): void
    {
        $plan = Plan::factory()->create(['trial_days' => 3, 'trial_sms_credits' => 0]);
        $market = Market::factory()->create();
        $service = new SubscriptionService();
        $service->startTrial($market, $plan);

        $this->travel(4)->days();
        $result = $service->processDaily();

        $this->assertSame(1, $result['expired']);
        $this->assertSame('expired', $market->fresh()->subscription_status);
        $this->assertFalse($market->fresh()->hasActiveSubscription());
    }

    /* ------------------------------------------------------------ admin UI */

    public function test_super_admin_can_activate_a_subscription_from_the_ui(): void
    {
        $plan = Plan::factory()->create();
        $market = Market::factory()->create();

        $this->actingAs($this->superAdmin())
            ->post(route('admin.markets.subscription.store', $market), [
                'plan_id' => $plan->id,
                'billing_cycle' => 'monthly',
                'amount_paid' => 1000,
                'payment_method' => 'nagad',
                'payment_reference' => 'NGD-1',
            ])
            ->assertRedirect(route('admin.markets.subscription', $market));

        $this->assertSame('active', $market->fresh()->subscription_status);
        $this->assertDatabaseHas('subscriptions', ['market_id' => $market->id, 'payment_reference' => 'NGD-1']);
    }

    public function test_admin_pages_render(): void
    {
        $plan = Plan::factory()->create(['name' => 'Gold Plan']);
        $market = Market::factory()->create(['plan_id' => $plan->id, 'subscription_status' => 'active', 'subscription_ends_at' => today()->addDays(3)]);
        $admin = $this->superAdmin();

        $this->actingAs($admin)->get(route('admin.plans.index'))->assertOk()->assertSee('Gold Plan');
        $this->actingAs($admin)->get(route('admin.plans.create'))->assertOk();
        $this->actingAs($admin)->get(route('admin.subscriptions.index'))->assertOk()->assertSee($market->name);
        $this->actingAs($admin)->get(route('admin.subscriptions.index', ['status' => 'expiring']))->assertOk()->assertSee($market->name);
        $this->actingAs($admin)->get(route('admin.markets.subscription', $market))->assertOk()->assertSee('Gold Plan');
        $this->actingAs($admin)->get(route('admin.markets.create'))->assertOk()->assertSee('Gold Plan');
        $this->actingAs($admin)->get(route('admin.dashboard'))->assertOk();
    }

    public function test_super_admin_can_create_and_edit_plans(): void
    {
        $admin = $this->superAdmin();

        $this->actingAs($admin)->post(route('admin.plans.store'), [
            'name' => 'Basic', 'monthly_price' => 800, 'yearly_price' => 8000, 'shop_limit' => 30,
            'sms_credits_per_month' => 100, 'trial_days' => 7, 'trial_sms_credits' => 10,
            'features' => ['masking_sms' => 1], 'is_active' => 1, 'is_default' => 1,
        ])->assertRedirect(route('admin.plans.index'));

        $plan = Plan::where('slug', 'basic')->firstOrFail();
        $this->assertTrue($plan->is_default);
        $this->assertTrue($plan->hasFeature('masking_sms'));
        $this->assertFalse($plan->hasFeature('pdf_reports'));

        $this->actingAs($admin)->put(route('admin.plans.update', $plan), [
            'name' => 'Basic', 'monthly_price' => 900, 'sms_credits_per_month' => 100,
            'trial_days' => 7, 'trial_sms_credits' => 10, 'is_active' => 1,
        ])->assertRedirect(route('admin.plans.index'));

        $this->assertSame(900.0, (float) $plan->fresh()->monthly_price);
        $this->assertNull($plan->fresh()->shop_limit);
    }

    public function test_market_owner_sees_subscription_card_in_settings(): void
    {
        $plan = Plan::factory()->create(['name' => 'Silver Plan', 'shop_limit' => 40]);
        $market = Market::factory()->create(['plan_id' => $plan->id, 'subscription_status' => 'trial', 'subscription_ends_at' => today()->addDays(5)]);

        $this->actingAs($this->owner($market))
            ->get(route('market-owner.settings.index'))
            ->assertOk()
            ->assertSee('Silver Plan')
            ->assertSee('0 / 40');

        $this->actingAs($this->owner($market))
            ->get(route('market-owner.dashboard'))
            ->assertOk()
            ->assertSee('5');
    }
}
