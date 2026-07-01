<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Media;
use App\Models\PointInjectionBatch;
use App\Models\Staff;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PointInjectionBatch>
 */
class PointInjectionBatchFactory extends Factory
{
    protected $model = PointInjectionBatch::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $totalRows = fake()->numberBetween(50, 500);
        $successfulRows = fake()->numberBetween(0, $totalRows);
        $failedRows = $totalRows - $successfulRows;

        return [
            'staff_id' => Staff::query()->inRandomOrder()->value('id') ?? Staff::factory(),
            'media_id' => Media::factory()->spreadsheet(),
            'total_rows' => $totalRows,
            'successful_rows' => $successfulRows,
            'failed_rows' => $failedRows,
            'total_points_injected' => fake()->numberBetween(10_000, 500_000),
            'uploaded_at' => fake()->dateTimeBetween('-3 months', 'now'),
        ];
    }
}
