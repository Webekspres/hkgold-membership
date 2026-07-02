<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\InjectionStatus;
use App\Models\Branch;
use App\Models\Media;
use App\Models\Member;
use App\Models\PointInjectionBatch;
use App\Models\PointInjectionDetail;
use App\Models\Staff;
use App\Models\TransactionType;
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

    /**
     * After creating the batch, attach realistic PointInjectionDetail rows.
     * Uses real member numbers and branch codes from the database when available.
     */
    public function withDetails(int $count = 10): self
    {
        return $this->afterCreating(function (PointInjectionBatch $batch) use ($count): void {
            $memberNumbers = Member::query()
                ->inRandomOrder()
                ->limit($count)
                ->pluck('member_number')
                ->toArray();

            if (empty($memberNumbers)) {
                return;
            }

            $transactionTypeIds = TransactionType::query()->pluck('id')->toArray();
            $branchCodes = Branch::query()->pluck('branch_code')->toArray();

            foreach (range(1, $count) as $rowNumber) {
                $memberNumber = $memberNumbers[($rowNumber - 1) % count($memberNumbers)];
                $transactionTypeId = fake()->randomElement($transactionTypeIds);
                $rawBranchCode = fake()->boolean(80)
                    ? fake()->randomElement($branchCodes)
                    : '';

                $purchaseNominal = fake()->numberBetween(100_000, 5_000_000);
                $calculatedPoints = (int) floor($purchaseNominal / 100_000);
                $status = fake()->randomElement([
                    InjectionStatus::Pending,
                    InjectionStatus::Success,
                    InjectionStatus::Failed,
                ]);

                PointInjectionDetail::query()->create([
                    'batch_id' => $batch->id,
                    'row_number' => $rowNumber,
                    'raw_member_number' => $memberNumber,
                    'raw_branch_code' => $rawBranchCode,
                    'purchase_nominal' => $purchaseNominal,
                    'transaction_type_id' => $transactionTypeId,
                    'transaction_date' => fake()->dateTimeBetween('-3 months', 'now'),
                    'calculated_points' => $calculatedPoints,
                    'status' => $status->value,
                    'error_message' => $status === InjectionStatus::Failed
                        ? fake()->sentence()
                        : null,
                    'processed_at' => $status !== InjectionStatus::Pending
                        ? fake()->dateTimeBetween('-3 months', 'now')
                        : null,
                    'receipt_number' => $status === InjectionStatus::Success
                        ? strtoupper(fake()->bothify('RCP-####-????'))
                        : null,
                ]);
            }
        });
    }
}
