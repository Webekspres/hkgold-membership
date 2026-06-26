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
            ['Cincin Emas 22K 2 gram', 'RW-CIN-22K-2G', 85000],
            ['Kalung Antam 1 gram', 'RW-KAL-ANT-1G', 120000],
            ['Voucher Belanja Rp 500.000', 'RW-VCR-500K', 45000],
            ['Gelang Emas 1 gram', 'RW-GEL-1G', 65000],
            ['Layanan Cuci Emas Premium', 'RW-CUC-PRM', 15000],
            ['Liontin Bulan Sabit 0.5 gram', 'RW-LIO-05G', 38000],
            ['Tas Merchandise HK Gold VIP', 'RW-TAS-MRC', 25000],
            ['Paket Tabungan Emas 0.25 gram', 'RW-TAB-025', 32000],
            ['Anting Emas 0.5 gram', 'RW-ANT-05G', 42000],
            ['Voucher Partner Rp 250.000', 'RW-VCR-250K', 22000],
            ['Gantungan Kunci Emas Mini', 'RW-GNT-MNI', 12000],
            ['Paket Hadiah Wedding 1 gram', 'RW-WED-1G', 95000],
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
