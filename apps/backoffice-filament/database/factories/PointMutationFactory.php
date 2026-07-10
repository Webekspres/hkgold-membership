<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\MutationType;
use App\Models\Branch;
use App\Models\Member;
use App\Models\PointInjectionBatch;
use App\Models\PointMutation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PointMutation>
 */
class PointMutationFactory extends Factory
{
    protected $model = PointMutation::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $type = fake()->randomElement(MutationType::cases());
        $transactionAmount = fake()->numberBetween(2_000_000, 50_000_000);
        $points = match ($type) {
            MutationType::Earn => round($transactionAmount / 10000, 2),
            MutationType::Redeem => fake()->numberBetween(5_000, 150_000),
            MutationType::Adjustment => fake()->numberBetween(-10_000, 10_000),
            MutationType::Expired => fake()->numberBetween(1_000, 25_000),
        };

        return [
            'member_id' => Member::factory(),
            'branch_id' => Branch::factory(),
            'batch_id' => fake()->boolean(40) ? PointInjectionBatch::factory() : null,
            'type' => $type,
            'points' => abs($points),
            'transaction_date' => fake()->dateTimeBetween('-6 months', 'now'),
            'description' => match ($type) {
                MutationType::Earn => 'Pembelian perhiasan emas 22K',
                MutationType::Redeem => 'Penukaran hadiah loyalitas',
                MutationType::Adjustment => 'Koreksi poin oleh backoffice',
                MutationType::Expired => 'Kadaluarsa poin tahunan',
            },
            'transaction_amount' => $transactionAmount,
            'invoice_reference' => fake()->optional(0.7)->regexify('INV-[0-9]{10}'),
            'upload_date' => fake()->dateTimeBetween('-6 months', 'now'),
        ];
    }
}
