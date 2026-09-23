<?php

namespace Database\Factories;

use App\Models\Market;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Market>
 */
class MarketFactory extends Factory
{
    protected $model = Market::class;

    public function definition(): array
    {
        $name = fake()->company() . ' Market';

        return [
            'name' => $name,
            'slug' => Str::slug($name) . '-' . Str::random(5),
            'phone' => '017' . fake()->numerify('########'),
            'status' => 'active',
            'sms_credits' => 0,
        ];
    }

    public function withOwnGateway(): static
    {
        return $this->state(fn () => ['sms_api_key' => 'own-key', 'sms_sender_id' => '8809600000000']);
    }

    public function withCredits(int $credits): static
    {
        return $this->state(fn () => ['sms_credits' => $credits]);
    }
}
