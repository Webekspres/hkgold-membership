<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\TierStatus;
use App\Models\Member;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Member>
 */
class MemberFactory extends Factory
{
    protected $model = Member::class;

    public function configure(): static
    {
        return $this->afterMaking(function (Member $member): void {
            if ($member->user_id !== null) {
                return;
            }

            $user = User::factory()->member()->create();
            $member->setAttribute('user_id', $user->id);
        });
    }

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $tier = fake()->randomElement(TierStatus::cases());
        $pointsByTier = [
            TierStatus::Silver->value => fake()->numberBetween(0, 49),
            TierStatus::Gold->value => fake()->numberBetween(50, 199),
            TierStatus::Platinum->value => fake()->numberBetween(200, 499),
            TierStatus::Elite->value => fake()->numberBetween(500, 2000),
        ];

        $pointBalance = $pointsByTier[$tier->value];
        $highestPoint = fake()->boolean(70)
            ? $pointBalance
            : fake()->numberBetween($pointBalance, $pointBalance + 10_000);

        return [
            // Default null: picking random existing branch IDs races with RefreshDatabase on shared MySQL.
            'registered_at_branch_id' => null,
            'address_id' => null,
            'member_number' => now()->format('ym').'-'.fake()->unique()->numerify('####'),
            'phone_number' => '08'.fake()->unique()->numerify('##########'),
            'current_tier' => $tier,
            'point_balance' => $pointBalance,
            'highest_point' => $highestPoint,
            'last_activity_at' => now(),
            'is_suspended' => false,
            'birth_date' => fake()->dateTimeBetween('-60 years', '-18 years'),
        ];
    }
}
