<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ActivityLog>
 */
class ActivityLogFactory extends Factory
{
    protected $model = ActivityLog::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'action' => 'manual_point_injection',
            'description' => 'Suntik poin manual oleh staff',
            'auditable_type' => 'PointMutation',
            'auditable_id' => fake()->uuid(),
            'before_json' => [
                'point_balance' => fake()->numberBetween(0, 5000),
                'highest_point' => fake()->numberBetween(0, 5000),
                'current_tier' => 'SILVER',
            ],
            'after_json' => [
                'point_balance' => fake()->numberBetween(5000, 10000),
                'highest_point' => fake()->numberBetween(5000, 10000),
                'current_tier' => 'GOLD',
            ],
            'ip_address' => fake()->ipv4(),
            'created_at' => now(),
        ];
    }
}
