<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\TierStatus;
use App\Models\Address;
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
            if ($member->id !== null) {
                return;
            }

            $user = User::factory()->customer()->create();
            $member->setAttribute('id', $user->id);
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

        return [
            'address_id' => Address::factory(),
            'member_code' => 'HK'.fake()->unique()->regexify('[A-Z]{1}[0-9]{7}'),
            'dob' => fake()->dateTimeBetween('-65 years', '-21 years')->format('Y-m-d'),
            'total_points' => $pointsByTier[$tier->value],
            'tier' => $tier,
            'phone_change_pending' => false,
        ];
    }
}
