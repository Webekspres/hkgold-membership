<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Member;
use App\Models\MemberAnomaly;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MemberAnomaly>
 */
class MemberAnomalyFactory extends Factory
{
    protected $model = MemberAnomaly::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'member_id' => Member::factory(),
            'last_mutation_at' => fake()->dateTimeBetween('-180 days', '-30 days'),
            'hoarded_points' => fake()->numberBetween(50_000, 500_000),
            'detected_at' => fake()->dateTimeBetween('-30 days', 'now'),
        ];
    }
}
