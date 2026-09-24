<?php

namespace Tests\Feature;

use App\Models\Plan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LandingPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_landing_is_bangla_by_default_with_contact_and_plans(): void
    {
        Plan::factory()->create(['name' => 'Starter', 'name_bn' => 'স্টার্টার', 'monthly_price' => 1000, 'shop_limit' => 50]);

        $this->get('/')
            ->assertOk()
            ->assertSee('lang="bn"', false)
            ->assertSee('কেন ডিউট্যাপ দরকার')
            ->assertSee('tel:+8801805995662')
            ->assertSee('wa.me/8801805995662')
            ->assertSee('০১৮০৫৯৯৫৬৬২')
            ->assertSee('স্টার্টার')
            ->assertSee(route('register'));
    }

    public function test_landing_switches_to_english(): void
    {
        $this->withSession(['locale' => 'en'])->get('/')
            ->assertOk()
            ->assertSee('lang="en"', false)
            ->assertSee('Why you need DueTap')
            ->assertSee('01805995662');
    }

    public function test_signed_in_users_skip_the_landing_page(): void
    {
        $user = User::factory()->create(['role' => 'shop_owner', 'is_active' => true]);

        $this->actingAs($user)->get('/')->assertRedirect(route('dashboard'));
    }
}
