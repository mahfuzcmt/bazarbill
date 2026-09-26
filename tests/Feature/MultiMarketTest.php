<?php

namespace Tests\Feature;

use App\Models\Market;
use App\Models\Plan;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class MultiMarketTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        foreach (['super_admin', 'market_owner', 'shop_owner'] as $r) {
            Role::findOrCreate($r);
        }
    }

    protected function owner(Market $market): User
    {
        $u = User::factory()->create(['role' => 'market_owner', 'market_id' => $market->id, 'is_active' => true, 'language_preference' => 'en']);
        $u->assignRole('market_owner');

        return $u;
    }

    public function test_membership_is_created_automatically_and_switching_rescopes_data(): void
    {
        $a = Market::factory()->create(['name' => 'Alpha Market']);
        $b = Market::factory()->create(['name' => 'Beta Market']);
        $owner = $this->owner($a);
        $this->assertTrue($owner->isMemberOf($a));
        $this->assertFalse($owner->isMemberOf($b));

        Shop::withoutGlobalScopes()->create(['market_id' => $a->id, 'shop_number' => 'A-1', 'rent_amount' => 100, 'shop_type' => 'general', 'status' => 'active']);
        Shop::withoutGlobalScopes()->create(['market_id' => $b->id, 'shop_number' => 'B-1', 'rent_amount' => 100, 'shop_type' => 'general', 'status' => 'active']);

        // Not a member: refused.
        $this->actingAs($owner)->post(route('market.switch', $b))->assertForbidden();

        $owner->markets()->attach($b->id);
        $this->actingAs($owner)->get(route('market-owner.shops.index'))->assertOk()->assertSee('A-1')->assertDontSee('B-1');

        $this->actingAs($owner)->post(route('market.switch', $b))->assertRedirect(route('dashboard'));
        $this->assertSame($b->id, $owner->fresh()->market_id);
        $this->actingAs($owner->fresh())->get(route('market-owner.shops.index'))->assertOk()->assertSee('B-1')->assertDontSee('A-1');

        // Sidebar shows the switcher with both markets.
        $this->actingAs($owner->fresh())->get(route('market-owner.dashboard'))->assertOk()->assertSee('Alpha Market')->assertSee('Beta Market');
    }

    public function test_owner_can_open_a_second_market_with_its_own_trial(): void
    {
        Plan::factory()->create(['is_default' => true, 'trial_days' => 14, 'trial_sms_credits' => 10]);
        $a = Market::factory()->create();
        $owner = $this->owner($a);

        $this->actingAs($owner)->get(route('market-owner.markets.index'))->assertOk()->assertSee($a->name);

        $this->actingAs($owner)->post(route('market-owner.markets.store'), ['name' => 'Second Market', 'name_bn' => 'দ্বিতীয় মার্কেট'])
            ->assertRedirect(route('market-owner.dashboard'));

        $second = Market::where('name', 'Second Market')->firstOrFail();
        $this->assertTrue($owner->fresh()->isMemberOf($second));
        $this->assertSame($second->id, $owner->fresh()->market_id);
        $this->assertSame('trial', $second->subscription_status);
        $this->assertSame(10, $second->sms_credits);
        $this->assertSame(2, $owner->markets()->count());
    }

    public function test_owner_can_add_and_remove_managers(): void
    {
        $market = Market::factory()->create();
        $owner = $this->owner($market);

        $this->actingAs($owner)->post(route('market-owner.managers.store'), [
            'name' => 'Secretary', 'phone' => '01755555555', 'password' => 'secret123', 'password_confirmation' => 'secret123',
        ])->assertRedirect(route('market-owner.managers.index'))->assertSessionHasNoErrors();

        $manager = User::where('phone', '01755555555')->firstOrFail();
        $this->assertSame('market_owner', $manager->role);
        $this->assertTrue($manager->hasRole('market_owner'));
        $this->assertTrue($manager->isMemberOf($market));

        // The manager sees the owner's data.
        Shop::withoutGlobalScopes()->create(['market_id' => $market->id, 'shop_number' => 'M-1', 'rent_amount' => 100, 'shop_type' => 'general', 'status' => 'active']);
        $this->actingAs($manager)->get(route('market-owner.shops.index'))->assertOk()->assertSee('M-1');

        // An owner of another market can be added by phone and keeps both.
        $other = Market::factory()->create();
        $partner = $this->owner($other);
        $partner->update(['phone' => '01766666666']);
        $this->actingAs($owner)->post(route('market-owner.managers.store'), ['name' => 'Partner', 'phone' => '01766666666'])->assertSessionHasNoErrors();
        $this->assertTrue($partner->fresh()->isMemberOf($market));
        $this->assertSame(2, $partner->markets()->count());

        // Removal: manager with no other market is locked; partner keeps the other market.
        $this->actingAs($owner)->delete(route('market-owner.managers.destroy', $manager))->assertRedirect();
        $this->assertFalse($manager->fresh()->is_active);
        $this->actingAs($owner)->delete(route('market-owner.managers.destroy', $partner))->assertRedirect();
        $this->assertTrue($partner->fresh()->is_active);
        $this->assertSame($other->id, $partner->fresh()->market_id);

        // Cannot remove yourself.
        $this->actingAs($owner)->from(route('market-owner.managers.index'))->delete(route('market-owner.managers.destroy', $owner))->assertSessionHas('error');
    }

    public function test_admin_can_grant_extra_markets_to_an_owner(): void
    {
        $a = Market::factory()->create();
        $b = Market::factory()->create();
        $owner = $this->owner($a);
        $admin = User::factory()->create(['role' => 'super_admin', 'is_active' => true]);
        $admin->assignRole('super_admin');

        $this->actingAs($admin)->put(route('admin.users.update', $owner), [
            'name' => $owner->name, 'email' => $owner->email, 'phone' => $owner->phone, 'role' => 'market_owner',
            'market_id' => $a->id, 'is_active' => 1, 'market_ids' => [$b->id],
        ])->assertRedirect(route('admin.users.index'));

        $this->assertEqualsCanonicalizing([$a->id, $b->id], $owner->markets()->pluck('markets.id')->all());
    }
}
