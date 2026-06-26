<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\TierStatus;
use App\Models\Branch;
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
            TierStatus::Silver->value => fake()->numberBetween(0, 49_999),
            TierStatus::Gold->value => fake()->numberBetween(50_000, 199_999),
            TierStatus::Platinum->value => fake()->numberBetween(200_000, 499_999),
            TierStatus::Sapphire->value => fake()->numberBetween(500_000, 2_000_000),
        ];

        $branchId = Branch::query()->inRandomOrder()->value('id');

        return [
            'registered_at_branch_id' => fake()->boolean(80) ? $branchId : null,
            'address_id' => null,
            'member_number' => 'HK'.fake()->unique()->regexify('[A-Z]{1}[0-9]{7}'),
            'phone_number' => '08'.fake()->unique()->numerify('##########'),
            'current_tier' => $tier,
            'point_balance' => $pointsByTier[$tier->value],
            'last_activity_at' => now(),
            'is_suspended' => false,
        ];
    }
}
