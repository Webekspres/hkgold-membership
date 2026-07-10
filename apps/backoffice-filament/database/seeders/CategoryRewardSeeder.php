<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\CategoryReward;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategoryRewardSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $categories = [
            'Perhiasan Emas',
            'Voucher Belanja',
            'Merchandise Eksklusif',
            'Layanan Cuci Emas',
            'Paket Investasi Emas',
            'Hadiah Ulang Tahun',
            'Aksesoris Premium',
            'Voucher Partner',
            'Souvenir Limited Edition',
            'Paket Hadiah Corporate',
        ];

        foreach ($categories as $name) {
            CategoryReward::query()->firstOrCreate(['name' => $name]);
        }
    }
}
