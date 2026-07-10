<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\BranchRewardStock;
use App\Models\Reward;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BranchRewardStockSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        if (Branch::query()->count() === 0) {
            $this->call(BranchSeeder::class);
        }

        if (Reward::query()->count() === 0) {
            $this->call(RewardSeeder::class);
        }

        $branches = Branch::query()->limit(10)->get();
        $rewards = Reward::query()->limit(10)->get();

        foreach ($branches as $branch) {
            foreach ($rewards->take(5) as $reward) {
                BranchRewardStock::query()->firstOrCreate(
                    [
                        'branch_id' => $branch->id,
                        'reward_id' => $reward->id,
                    ],
                    ['stock_quantity' => fake()->numberBetween(2, 40)],
                );
            }
        }
    }
}
