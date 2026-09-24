<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\Market;
use App\Models\Payment;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ShopOwnerStatementTest extends TestCase
{
    use RefreshDatabase;

    public function test_shop_owner_sees_month_bill_payment_dates_and_due_for_all_their_shops(): void
    {
        Role::findOrCreate('shop_owner');
        $market = Market::factory()->create();
        $owner = User::factory()->create(['role' => 'shop_owner', 'market_id' => $market->id, 'is_active' => true, 'language_preference' => 'en']);
        $owner->assignRole('shop_owner');
        $collector = User::factory()->create(['role' => 'collector', 'market_id' => $market->id]);

        $a = Shop::withoutGlobalScopes()->create(['market_id' => $market->id, 'shop_owner_id' => $owner->id, 'shop_number' => 'A-1', 'rent_amount' => 5000, 'shop_type' => 'general', 'status' => 'active']);
        $b = Shop::withoutGlobalScopes()->create(['market_id' => $market->id, 'shop_owner_id' => $owner->id, 'shop_number' => 'B-2', 'rent_amount' => 3000, 'shop_type' => 'general', 'status' => 'active']);
        $other = Shop::withoutGlobalScopes()->create(['market_id' => $market->id, 'shop_number' => 'C-3', 'rent_amount' => 1000, 'shop_type' => 'general', 'status' => 'active']);

        $invA = Invoice::create(['market_id' => $market->id, 'shop_id' => $a->id, 'billing_month' => '2026-08', 'rent_amount' => 5000, 'total_amount' => 5000, 'paid_amount' => 0, 'due_amount' => 5000, 'status' => 'pending', 'due_date' => '2026-08-10']);
        Payment::create(['invoice_id' => $invA->id, 'market_id' => $market->id, 'shop_id' => $a->id, 'collected_by' => $collector->id, 'amount' => 2000, 'payment_method' => 'cash', 'payment_date' => '2026-08-05']);
        Invoice::create(['market_id' => $market->id, 'shop_id' => $b->id, 'billing_month' => '2026-08', 'rent_amount' => 3000, 'total_amount' => 3000, 'paid_amount' => 0, 'due_amount' => 3000, 'status' => 'pending', 'due_date' => '2026-08-10']);
        $foreign = Invoice::create(['market_id' => $market->id, 'shop_id' => $other->id, 'billing_month' => '2026-08', 'rent_amount' => 1000, 'total_amount' => 1000, 'paid_amount' => 0, 'due_amount' => 1000, 'status' => 'pending', 'due_date' => '2026-08-10']);

        $page = $this->actingAs($owner)->get(route('shop-owner.invoices.index'));
        $page->assertOk()
            ->assertSee('A-1')->assertSee('B-2')->assertDontSee('C-3')
            ->assertSee('05 Aug 2026')          // when it was paid
            ->assertSee('No payment yet')       // the other shop's invoice
            ->assertSee('৳6,000');              // total due across both shops

        $this->actingAs($owner)->get(route('shop-owner.invoices.index', ['shop_id' => $b->id]))->assertOk()->assertDontSee($invA->invoice_number)->assertSee('B-2');
        $this->actingAs($owner)->get(route('shop-owner.dashboard'))->assertOk()->assertSee('A-1, B-2')->assertSee('৳8,000');
        $this->actingAs($owner)->get(route('shop-owner.invoices.show', $foreign))->assertForbidden();
        $this->actingAs($owner)->get(route('shop-owner.invoices.show', $invA))->assertOk();
    }
}
