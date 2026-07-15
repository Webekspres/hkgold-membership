<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Branch;
use App\Models\Member;
use App\Models\RedeemToken;
use App\Models\Reward;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RedeemToken>
 */
class RedeemTokenFactory extends Factory
{
    protected $model = RedeemToken::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $reward = Reward::query()->inRandomOrder()->first();

        return [
            'member_id' => Member::query()->inRandomOrder()->value('id') ?? Member::factory(),
            'reward_id' => $reward?->id ?? Reward::factory(),
            'branch_id' => Branch::query()->inRandomOrder()->value('id') ?? Branch::factory(),
            'token_code' => fake()->unique()->regexify('[A-Z0-9]{10}'),
            'held_points' => $reward?->points_required ?? fake()->numberBetween(15_000, 200_000),
            'is_used' => false,
            'expired_at' => now()->addMinutes(30),
        ];
    }

    public function used(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_used' => true,
            'expired_at' => now()->addMinutes(30),
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_used' => false,
            'expired_at' => now()->subMinutes(5),
        ]);
    }
}
