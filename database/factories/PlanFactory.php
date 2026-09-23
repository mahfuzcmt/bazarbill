<?php

namespace Database\Factories;

use App\Models\Plan;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Plan>
 */
class PlanFactory extends Factory
{
    protected $model = Plan::class;

    public function definition(): array
    {
        $name = ucfirst(fake()->unique()->word()) . ' Plan';

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'monthly_price' => 1000,
            'yearly_price' => 10000,
            'shop_limit' => 50,
            'sms_credits_per_month' => 200,
            'trial_days' => 14,
            'trial_sms_credits' => 20,
            'features' => ['masking_sms' => false],
            'is_default' => false,
            'is_active' => true,
            'sort_order' => 0,
        ];
    }
}
