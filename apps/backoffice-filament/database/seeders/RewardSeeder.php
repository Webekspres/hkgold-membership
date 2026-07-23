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
            ['Cincin Emas 22K 2 gram', 'RW-CIN-22K-2G', 85],
            ['Kalung Antam 1 gram', 'RW-KAL-ANT-1G', 120],
            ['Voucher Belanja Rp 500.000', 'RW-VCR-500K', 45],
            ['Gelang Emas 1 gram', 'RW-GEL-1G', 65],
            ['Layanan Cuci Emas Premium', 'RW-CUC-PRM', 15],
            ['Liontin Bulan Sabit 0.5 gram', 'RW-LIO-05G', 38],
            ['Tas Merchandise HK Gold VIP', 'RW-TAS-MRC', 25],
            ['Paket Tabungan Emas 0.25 gram', 'RW-TAB-025', 32],
            ['Anting Emas 0.5 gram', 'RW-ANT-05G', 42],
            ['Voucher Partner Rp 250.000', 'RW-VCR-250K', 22],
            ['Gantungan Kunci Emas Mini', 'RW-GNT-MNI', 12],
            ['Paket Hadiah Wedding 1 gram', 'RW-WED-1G', 95],
        ];

        foreach ($rewards as [$name, $sku, $points]) {
            Reward::query()->firstOrCreate(
                ['sku' => $sku],
                [
                    'category_id' => $categoryId,
                    'name' => $name,
                    'points_required' => $points,
                    'description' => 'Hadiah loyalitas HK GOLD VIP.',
                    'is_active' => true,
                    'start_at' => now()->subMonth(),
                    'end_at' => now()->addYear(),
                ],
            );
        }
    }
}
