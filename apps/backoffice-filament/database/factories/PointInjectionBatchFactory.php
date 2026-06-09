<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\BatchStatus;
use App\Models\Branch;
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
        $status = fake()->randomElement(BatchStatus::cases());
        $totalRows = fake()->numberBetween(50, 500);
        $processedRows = match ($status) {
            BatchStatus::Completed => $totalRows,
            BatchStatus::Processing => fake()->numberBetween(1, $totalRows - 1),
            BatchStatus::Failed => fake()->numberBetween(0, (int) ($totalRows * 0.5)),
            default => 0,
        };

        return [
            'branch_id' => Branch::factory(),
            'uploaded_by_id' => Staff::factory(),
            'filename' => 'inject-poin-'.fake()->date('Ymd').'.xlsx',
            'file_url' => 'https://cdn.hkgoldvip.id/batches/'.fake()->uuid().'.xlsx',
            'status' => $status,
            'total_rows' => $totalRows,
            'processed_rows' => $processedRows,
            'error_log' => $status === BatchStatus::Failed ? 'Baris 42: member_code tidak ditemukan' : null,
        ];
    }
}
