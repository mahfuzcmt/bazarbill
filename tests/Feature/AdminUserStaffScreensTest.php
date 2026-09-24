<?php

namespace Tests\Feature;

use App\Models\Market;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminUserStaffScreensTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        foreach (['super_admin', 'market_owner', 'collector'] as $role) {
            Role::findOrCreate($role);
        }
    }

    public function test_staff_create_form_has_confirm_field_and_saves(): void
    {
        $market = Market::factory()->create();
        $owner = User::factory()->create(['role' => 'market_owner', 'market_id' => $market->id, 'is_active' => true]);
        $owner->assignRole('market_owner');

        $this->actingAs($owner)->get(route('market-owner.staff.create'))
            ->assertOk()->assertSee('name="password_confirmation"', false);

        $this->actingAs($owner)->post(route('market-owner.staff.store'), [
            'name' => 'Collector One', 'email' => 'c1@example.com', 'phone' => '01711111111',
            'password' => 'secret123', 'password_confirmation' => 'secret123',
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('users', ['email' => 'c1@example.com', 'role' => 'collector', 'market_id' => $market->id]);
    }

    public function test_admin_user_pages_link_to_market_sms_credits(): void
    {
        $market = Market::factory()->withCredits(77)->create();
        $owner = User::factory()->create(['role' => 'market_owner', 'market_id' => $market->id]);
        $admin = User::factory()->create(['role' => 'super_admin', 'is_active' => true]);
        $admin->assignRole('super_admin');

        $this->actingAs($admin)->get(route('admin.users.show', $owner))
            ->assertOk()->assertSee('77')->assertSee(route('admin.markets.sms-credits', $market));

        $this->actingAs($admin)->get(route('admin.users.index'))
            ->assertOk()->assertSee(route('admin.markets.sms-credits', $market));
    }
}
