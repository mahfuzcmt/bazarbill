<?php

namespace Tests\Feature;

use App\Models\Market;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PhoneLoginAndAdminShopOwnerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        foreach (['super_admin', 'market_owner', 'shop_owner', 'collector'] as $r) {
            Role::findOrCreate($r);
        }
    }

    public function test_user_can_log_in_with_mobile_number_in_any_format(): void
    {
        $market = Market::factory()->create();
        $user = User::factory()->create([
            'role' => 'shop_owner', 'market_id' => $market->id, 'phone' => '01712345678',
            'email' => User::placeholderEmail('01712345678', $market->id), 'is_active' => true,
        ]);

        foreach (['01712345678', '+8801712345678', '8801712345678', '017-1234-5678'] as $input) {
            auth()->logout();
            $this->post('/login', ['login' => $input, 'password' => 'password'])->assertSessionHasNoErrors();
            $this->assertAuthenticatedAs($user);
        }
    }

    public function test_same_phone_in_two_markets_requires_email_login(): void
    {
        $a = Market::factory()->create();
        $b = Market::factory()->create();
        $ua = User::factory()->create(['role' => 'shop_owner', 'market_id' => $a->id, 'phone' => '01799999999', 'email' => User::placeholderEmail('01799999999', $a->id), 'is_active' => true]);
        $ub = User::factory()->create(['role' => 'shop_owner', 'market_id' => $b->id, 'phone' => '01799999999', 'email' => User::placeholderEmail('01799999999', $b->id), 'is_active' => true]);

        $this->assertNotSame($ua->email, $ub->email); // placeholders never collide across markets

        $this->post('/login', ['login' => '01799999999', 'password' => 'password'])->assertSessionHasErrors('login');
        $this->assertGuest();

        $this->post('/login', ['login' => $ub->email, 'password' => 'password'])->assertSessionHasNoErrors();
        $this->assertAuthenticatedAs($ub);
    }

    public function test_inactive_user_cannot_log_in_by_phone(): void
    {
        User::factory()->create(['phone' => '01711111111', 'is_active' => false]);
        $this->post('/login', ['login' => '01711111111', 'password' => 'password'])->assertSessionHasErrors('login');
        $this->assertGuest();
    }

    public function test_super_admin_creates_shop_owner_with_shops_and_no_email(): void
    {
        $admin = User::factory()->create(['role' => 'super_admin', 'is_active' => true, 'language_preference' => 'en']);
        $admin->assignRole('super_admin');
        $market = Market::factory()->create();
        $free = Shop::withoutGlobalScopes()->create(['market_id' => $market->id, 'shop_number' => 'Z-1', 'rent_amount' => 500, 'shop_type' => 'general', 'status' => 'active']);
        $other = Market::factory()->create();
        $foreign = Shop::withoutGlobalScopes()->create(['market_id' => $other->id, 'shop_number' => 'Q-1', 'rent_amount' => 500, 'shop_type' => 'general', 'status' => 'active']);

        $this->actingAs($admin)->get(route('admin.users.create'))->assertOk()->assertSee('Z-1')->assertSee('shop-picker');

        $this->actingAs($admin)->post(route('admin.users.store'), [
            'name' => 'Tenant Two', 'phone' => '01722222222', 'email' => '',
            'password' => 'Password!123', 'password_confirmation' => 'Password!123',
            'role' => 'shop_owner', 'market_id' => $market->id, 'is_active' => 1,
            'shop_ids' => [$free->id, $foreign->id],
        ])->assertRedirect(route('admin.users.index'))->assertSessionHasNoErrors();

        $user = User::where('phone', '01722222222')->firstOrFail();
        $this->assertSame(User::placeholderEmail('01722222222', $market->id), $user->email);
        $this->assertSame($user->id, $free->fresh()->shop_owner_id);
        $this->assertNull($foreign->fresh()->shop_owner_id); // other market's shop untouched

        // Edit: changing role away from shop owner releases the shops
        $this->actingAs($admin)->put(route('admin.users.update', $user), [
            'name' => 'Tenant Two', 'phone' => '01722222222', 'role' => 'collector', 'market_id' => $market->id, 'is_active' => 1,
        ])->assertRedirect(route('admin.users.index'))->assertSessionHasNoErrors();
        $this->assertNull($free->fresh()->shop_owner_id);
    }

    public function test_admin_user_requires_email_or_phone_and_market_for_non_admins(): void
    {
        $admin = User::factory()->create(['role' => 'super_admin', 'is_active' => true]);
        $admin->assignRole('super_admin');

        $this->actingAs($admin)->post(route('admin.users.store'), [
            'name' => 'X', 'password' => 'Password!123', 'password_confirmation' => 'Password!123', 'role' => 'market_owner',
        ])->assertSessionHasErrors(['phone', 'market_id']);
    }
}
