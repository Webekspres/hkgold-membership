<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Branch;
use App\Models\BranchRewardStock;
use App\Models\Reward;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BranchRewardStock>
 */
class BranchRewardStockFactory extends Factory
{
    protected $model = BranchRewardStock::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'branch_id' => Branch::factory(),
            'reward_id' => Reward::factory(),
            'actual_stock' => fake()->numberBetween(0, 50),
            'held_stock' => fake()->numberBetween(0, 10),
        ];
    }
}
