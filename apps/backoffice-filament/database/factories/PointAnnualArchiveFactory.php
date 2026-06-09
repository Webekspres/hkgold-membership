<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Member;
use App\Models\PointAnnualArchive;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PointAnnualArchive>
 */
class PointAnnualArchiveFactory extends Factory
{
    protected $model = PointAnnualArchive::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'member_id' => Member::factory(),
            'points_snapshot' => fake()->numberBetween(0, 1_500_000),
            'archive_year' => fake()->numberBetween(2020, (int) date('Y') - 1),
        ];
    }
}
