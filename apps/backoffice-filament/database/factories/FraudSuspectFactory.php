<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\FraudSuspect;
use App\Models\Member;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FraudSuspect>
 */
class FraudSuspectFactory extends Factory
{
    protected $model = FraudSuspect::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $members = Member::factory()->count(2)->create();

        return [
            'detected_name' => fake('id_ID')->name(),
            'detected_birth_date' => fake()->dateTimeBetween('-65 years', '-21 years'),
            'suspect_member_ids' => $members->pluck('id')->all(),
            'status' => fake()->randomElement(['PENDING_REVIEW', 'CLEARED', 'SUSPENDED']),
            'admin_notes' => fake()->optional(0.5)->sentence(),
        ];
    }
}
