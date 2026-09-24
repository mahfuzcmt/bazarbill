<?php

namespace Tests\Feature;

use App\Models\Market;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ShopOwnerManagementTest extends TestCase
{
    use RefreshDatabase;

    protected Market $market;
    protected User $owner;

    protected function setUp(): void
    {
        parent::setUp();
        foreach (['market_owner', 'shop_owner'] as $role) {
            Role::findOrCreate($role);
        }
        $this->market = Market::factory()->create();
        $this->owner = User::factory()->create(['role' => 'market_owner', 'market_id' => $this->market->id, 'is_active' => true, 'language_preference' => 'en']);
        $this->owner->assignRole('market_owner');
    }

    protected function shop(string $number, ?int $ownerId = null, ?int $marketId = null): Shop
    {
        return Shop::withoutGlobalScopes()->create([
            'market_id' => $marketId ?? $this->market->id, 'shop_owner_id' => $ownerId,
            'shop_number' => $number, 'rent_amount' => 1000, 'shop_type' => 'general', 'status' => 'active',
        ]);
    }

    public function test_market_owner_can_create_shop_owner_and_assign_shops(): void
    {
        $free = $this->shop('A-1');
        $taken = $this->shop('A-2', User::factory()->create(['role' => 'shop_owner', 'market_id' => $this->market->id])->id);

        $this->actingAs($this->owner)->get(route('market-owner.shop-owners.create'))
            ->assertOk()->assertSee('A-1')->assertDontSee('A-2');

        $this->actingAs($this->owner)->post(route('market-owner.shop-owners.store'), [
            'name' => 'Abdul Karim', 'name_bn' => 'আব্দুল করিম', 'phone' => '01712345678',
            'password' => 'secret123', 'password_confirmation' => 'secret123',
            'shop_ids' => [$free->id, $taken->id],
        ])->assertRedirect(route('market-owner.shop-owners.index'))->assertSessionHasNoErrors();

        $user = User::where('phone', '01712345678')->firstOrFail();
        $this->assertSame('shop_owner', $user->role);
        $this->assertTrue($user->hasRole('shop_owner'));
        $this->assertSame($this->market->id, $user->market_id);
        $this->assertSame(User::placeholderEmail('01712345678', $this->market->id), $user->email);
        $this->assertSame($user->id, $free->fresh()->shop_owner_id);
        $this->assertNotSame($user->id, $taken->fresh()->shop_owner_id); // already-owned shop is not stolen
    }

    public function test_validation_requires_confirmation_and_unique_phone_in_market(): void
    {
        User::factory()->create(['role' => 'shop_owner', 'market_id' => $this->market->id, 'phone' => '01799999999']);

        $this->actingAs($this->owner)->post(route('market-owner.shop-owners.store'), [
            'name' => 'X', 'phone' => '01799999999', 'password' => 'secret123', 'password_confirmation' => 'different',
        ])->assertSessionHasErrors(['phone', 'password']);
    }

    public function test_edit_updates_password_shops_and_status(): void
    {
        $tenant = User::factory()->create(['role' => 'shop_owner', 'market_id' => $this->market->id, 'phone' => '01711111111', 'is_active' => true]);
        $a = $this->shop('B-1', $tenant->id);
        $b = $this->shop('B-2');

        $this->actingAs($this->owner)->get(route('market-owner.shop-owners.edit', $tenant))->assertOk()->assertSee('B-1')->assertSee('B-2');

        $this->actingAs($this->owner)->put(route('market-owner.shop-owners.update', $tenant), [
            'name' => 'Renamed', 'phone' => '01711111111', 'email' => 'tenant@example.com',
            'password' => 'newpass123', 'password_confirmation' => 'newpass123',
            'shop_ids' => [$b->id], // drop B-1, take B-2
        ])->assertRedirect(route('market-owner.shop-owners.index'))->assertSessionHasNoErrors();

        $tenant->refresh();
        $this->assertSame('Renamed', $tenant->name);
        $this->assertFalse($tenant->is_active); // checkbox absent = inactive
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('newpass123', $tenant->password));
        $this->assertNull($a->fresh()->shop_owner_id);
        $this->assertSame($tenant->id, $b->fresh()->shop_owner_id);

        $this->actingAs($this->owner)->post(route('market-owner.shop-owners.toggle-status', $tenant))->assertRedirect();
        $this->assertTrue($tenant->fresh()->is_active);
    }

    public function test_cannot_touch_shop_owners_of_another_market(): void
    {
        $other = Market::factory()->create();
        $foreign = User::factory()->create(['role' => 'shop_owner', 'market_id' => $other->id]);

        $this->actingAs($this->owner)->get(route('market-owner.shop-owners.edit', $foreign))->assertNotFound();
        $this->actingAs($this->owner)->delete(route('market-owner.shop-owners.destroy', $foreign))->assertNotFound();
        $this->assertDatabaseHas('users', ['id' => $foreign->id]);
    }

    public function test_index_lists_owners_with_their_shops(): void
    {
        $tenant = User::factory()->create(['role' => 'shop_owner', 'market_id' => $this->market->id, 'name' => 'Listed Tenant']);
        $this->shop('C-7', $tenant->id);
        $this->shop('C-8');

        $this->actingAs($this->owner)->get(route('market-owner.shop-owners.index'))
            ->assertOk()->assertSee('Listed Tenant')->assertSee('C-7')->assertSee('1 shops have no owner yet');
    }
}
