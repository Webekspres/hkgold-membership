<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\CategoryReward;
use App\Models\Reward;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RewardSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        if (CategoryReward::query()->count() === 0) {
            $this->call(CategoryRewardSeeder::class);
        }

        $categoryId = CategoryReward::query()->value('id');

        $rewards = [
            ['Cincin Emas 22K 2 gram', 85000],
            ['Kalung Antam 1 gram', 120000],
            ['Voucher Belanja Rp 500.000', 45000],
            ['Gelang Emas 1 gram', 65000],
            ['Layanan Cuci Emas Premium', 15000],
            ['Liontin Bulan Sabit 0.5 gram', 38000],
            ['Tas Merchandise HK Gold VIP', 25000],
            ['Paket Tabungan Emas 0.25 gram', 32000],
            ['Anting Emas 0.5 gram', 42000],
            ['Voucher Partner Rp 250.000', 22000],
            ['Gantungan Kunci Emas Mini', 12000],
            ['Paket Hadiah Wedding 1 gram', 95000],
        ];

        foreach ($rewards as [$name, $points]) {
            Reward::query()->firstOrCreate(
                ['name' => $name],
                [
                    'category_reward_id' => $categoryId,
                    'points_required' => $points,
                    'description' => 'Hadiah loyalitas HK GOLD VIP.',
                    'is_active' => true,
                ],
            );
        }
    }
}
