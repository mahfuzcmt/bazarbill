<?php

namespace Database\Seeders;

use App\Models\Market;
use App\Models\User;
use App\Models\Shop;
use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        // Create demo market
        $market = Market::create([
            'name' => 'Dhaka Central Market',
            'name_bn' => 'ঢাকা সেন্ট্রাল মার্কেট',
            'slug' => 'dhaka-central-market',
            'address' => '123 Market Road, Dhaka 1000',
            'address_bn' => '১২৩ মার্কেট রোড, ঢাকা ১০০০',
            'phone' => '01700000000',
            'email' => 'info@dhakacentralmarket.com',
            // No own API key: the demo market sends through the platform gateway on prepaid credits.
            'sms_templates' => [
                'invoice_generated' => 'প্রিয় {shop_owner}, আপনার {month} মাসের ভাড়া {amount} টাকা। বিল নং: {invoice_no}',
                'payment_reminder' => 'প্রিয় {shop_owner}, আপনার {amount} টাকা বকেয়া আছে। অনুগ্রহ করে পরিশোধ করুন।',
                'payment_received' => 'ধন্যবাদ! {amount} টাকা পেমেন্ট গৃহীত হয়েছে। রসিদ নং: {receipt_no}',
            ],
            'settings' => [
                'allow_collector_edit_rent' => false,
                'invoice_due_days' => 15,
                'late_fee_percentage' => 5,
            ],
            'status' => 'active',
        ]);

        // Create super admin (no market)
        $superAdmin = User::create([
            'name' => 'Super Admin',
            'name_bn' => 'সুপার এডমিন',
            'email' => 'admin@duetap.com',
            'phone' => '01800000000',
            'password' => Hash::make('password'),
            'role' => 'super_admin',
            'is_active' => true,
            'language_preference' => 'en',
        ]);
        $superAdmin->assignRole('super_admin');

        // Put the demo market on a paid Standard plan (SMS allowance is credited automatically).
        $standard = \App\Models\Plan::where('slug', 'standard')->first() ?? \App\Models\Plan::default();
        if ($standard) {
            app(\App\Services\SubscriptionService::class)->activate($market, $standard, 'monthly', [
                'payment_method' => 'bkash',
                'payment_reference' => 'DEMO-TRX-001',
                'notes' => 'Demo subscription',
            ], $superAdmin->id);
        }

        // Create market owner
        $marketOwner = User::create([
            'market_id' => $market->id,
            'name' => 'Rahim Uddin',
            'name_bn' => 'রহিম উদ্দিন',
            'email' => 'owner@dhakacentralmarket.com',
            'phone' => '01711111111',
            'password' => Hash::make('password'),
            'role' => 'market_owner',
            'is_active' => true,
            'language_preference' => 'bn',
        ]);
        $marketOwner->assignRole('market_owner');

        // Create collector
        $collector = User::create([
            'market_id' => $market->id,
            'name' => 'Karim Ali',
            'name_bn' => 'করিম আলী',
            'email' => 'collector@dhakacentralmarket.com',
            'phone' => '01722222222',
            'password' => Hash::make('password'),
            'role' => 'collector',
            'is_active' => true,
            'language_preference' => 'bn',
        ]);
        $collector->assignRole('collector');

        // Create shop owners and shops
        $shopOwners = [
            ['name' => 'Mohammad Hasan', 'name_bn' => 'মোহাম্মদ হাসান', 'phone' => '01733333333'],
            ['name' => 'Abdul Karim', 'name_bn' => 'আব্দুল করিম', 'phone' => '01744444444'],
            ['name' => 'Fatema Begum', 'name_bn' => 'ফাতেমা বেগম', 'phone' => '01755555555'],
            ['name' => 'Jamal Ahmed', 'name_bn' => 'জামাল আহমেদ', 'phone' => '01766666666'],
            ['name' => 'Sufia Khatun', 'name_bn' => 'সুফিয়া খাতুন', 'phone' => '01777777777'],
        ];

        $shopTypes = ['general', 'clothing', 'electronics', 'food', 'jewelry'];
        $floors = ['Ground Floor', '1st Floor', '2nd Floor'];

        foreach ($shopOwners as $index => $ownerData) {
            $shopOwner = User::create([
                'market_id' => $market->id,
                'name' => $ownerData['name'],
                'name_bn' => $ownerData['name_bn'],
                'email' => strtolower(str_replace(' ', '.', $ownerData['name'])) . '@example.com',
                'phone' => $ownerData['phone'],
                'password' => Hash::make('password'),
                'role' => 'shop_owner',
                'is_active' => true,
                'language_preference' => 'bn',
            ]);
            $shopOwner->assignRole('shop_owner');

            $rentAmount = rand(5, 15) * 1000; // 5000 to 15000

            $shop = Shop::withoutGlobalScopes()->create([
                'market_id' => $market->id,
                'shop_owner_id' => $shopOwner->id,
                'collector_id' => $collector->id,
                'shop_number' => 'S-' . str_pad($index + 1, 3, '0', STR_PAD_LEFT),
                'floor' => $floors[array_rand($floors)],
                'area_sqft' => rand(50, 200),
                'rent_amount' => $rentAmount,
                'advance_deposit' => $rentAmount * 2,
                'shop_type' => $shopTypes[$index],
                'status' => 'active',
            ]);

            // Create invoice for current month
            $billingMonth = now()->format('Y-m');
            $invoice = Invoice::withoutGlobalScopes()->create([
                'market_id' => $market->id,
                'shop_id' => $shop->id,
                'billing_month' => $billingMonth,
                'rent_amount' => $shop->rent_amount,
                'previous_due' => 0,
                'discount' => 0,
                'late_fee' => 0,
                'total_amount' => $shop->rent_amount,
                'paid_amount' => 0,
                'due_amount' => $shop->rent_amount,
                'status' => 'pending',
                'due_date' => now()->addDays(15),
            ]);

            // Create some payments for first 3 shops
            if ($index < 3) {
                $paymentAmount = $index < 2 ? $shop->rent_amount : $shop->rent_amount / 2;
                Payment::withoutGlobalScopes()->create([
                    'invoice_id' => $invoice->id,
                    'market_id' => $market->id,
                    'shop_id' => $shop->id,
                    'collected_by' => $collector->id,
                    'amount' => $paymentAmount,
                    'payment_method' => 'cash',
                    'payment_date' => now()->subDays(rand(1, 5)),
                ]);
            }
        }

        // Create a vacant shop
        Shop::withoutGlobalScopes()->create([
            'market_id' => $market->id,
            'shop_number' => 'S-006',
            'floor' => '2nd Floor',
            'area_sqft' => 100,
            'rent_amount' => 8000,
            'advance_deposit' => 16000,
            'shop_type' => 'general',
            'status' => 'vacant',
        ]);
    }
}
