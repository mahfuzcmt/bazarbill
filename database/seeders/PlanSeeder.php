<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    /**
     * Starter pricing for the Bangladesh market. Edit freely from Admin → Plans.
     */
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Starter',
                'name_bn' => 'স্টার্টার',
                'slug' => 'starter',
                'description' => 'For small markets and single buildings.',
                'monthly_price' => 1000,
                'yearly_price' => 10000,
                'shop_limit' => 50,
                'sms_credits_per_month' => 200,
                'trial_days' => 14,
                'trial_sms_credits' => 20,
                'features' => ['masking_sms' => false, 'pdf_reports' => true, 'complaints' => true, 'notices' => true],
                'is_default' => true,
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Standard',
                'name_bn' => 'স্ট্যান্ডার্ড',
                'slug' => 'standard',
                'description' => 'For mid-size markets with several collectors.',
                'monthly_price' => 2500,
                'yearly_price' => 25000,
                'shop_limit' => 200,
                'sms_credits_per_month' => 1000,
                'trial_days' => 14,
                'trial_sms_credits' => 20,
                'features' => ['masking_sms' => true, 'pdf_reports' => true, 'complaints' => true, 'notices' => true],
                'is_default' => false,
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Enterprise',
                'name_bn' => 'এন্টারপ্রাইজ',
                'slug' => 'enterprise',
                'description' => 'Unlimited shops for large markets and market committees.',
                'monthly_price' => 5000,
                'yearly_price' => 50000,
                'shop_limit' => null,
                'sms_credits_per_month' => 3000,
                'trial_days' => 14,
                'trial_sms_credits' => 20,
                'features' => ['masking_sms' => true, 'pdf_reports' => true, 'complaints' => true, 'notices' => true],
                'is_default' => false,
                'is_active' => true,
                'sort_order' => 3,
            ],
        ];

        foreach ($plans as $plan) {
            Plan::updateOrCreate(['slug' => $plan['slug']], $plan);
        }
    }
}
