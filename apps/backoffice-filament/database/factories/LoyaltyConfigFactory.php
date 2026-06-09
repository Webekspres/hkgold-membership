<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\TierStatus;
use App\Models\LoyaltyConfig;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LoyaltyConfig>
 */
class LoyaltyConfigFactory extends Factory
{
    protected $model = LoyaltyConfig::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tier' => fake()->randomElement(TierStatus::cases()),
            'multiplier_cost' => fake()->randomElement([10000, 12500, 15000, 20000]),
        ];
    }
}
