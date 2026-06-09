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
        $lastActive = fake()->dateTimeBetween('-180 days', '-90 days');
        $daysInactive = (int) now()->diffInDays($lastActive);

        return [
            'member_id' => Member::factory(),
            'last_active_at' => $lastActive,
            'inactivity_duration_days' => $daysInactive,
        ];
    }
}
