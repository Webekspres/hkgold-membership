<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Branch;
use App\Models\Member;
use App\Models\PointMutation;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\DB;

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
        $transactionAmount = fake()->numberBetween(2_000_000, 50_000_000);
        $pointsIssued = fake()->numberBetween(1_000, 50_000);
        $pointsRedeemed = fake()->boolean(30) ? fake()->numberBetween(500, 10_000) : 0;
        $transactionTypeId = DB::table('transaction_types')->value('id');
        $branchId = Branch::query()->inRandomOrder()->value('id');

        return [
            'member_id' => Member::query()->inRandomOrder()->value('id') ?? Member::factory(),
            'branch_id' => fake()->optional(0.8)->passthrough($branchId ?? Branch::factory()),
            'reference_id' => fake()->optional(0.7)->regexify('INV-[0-9]{10}'),
            'transaction_type_id' => $transactionTypeId,
            'purchase_nominal' => $transactionAmount,
            'points_issued' => $pointsIssued,
            'points_redeemed' => $pointsRedeemed,
            'balance_snapshot' => $pointsIssued - $pointsRedeemed,
            'transaction_date' => fake()->dateTimeBetween('-6 months', 'now'),
            'uploaded_at' => fake()->dateTimeBetween('-6 months', 'now'),
        ];
    }
}
