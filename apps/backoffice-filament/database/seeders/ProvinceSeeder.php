<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Province;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProvinceSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $provinces = [
            'Jawa Tengah',
            'Jawa Timur',
            'DKI Jakarta',
            'Jawa Barat',
            'DI Yogyakarta',
            'Kalimantan Barat',
            'Sumatera Utara',
            'Bali',
            'Sulawesi Selatan',
            'Banten',
        ];

        foreach ($provinces as $name) {
            Province::query()->firstOrCreate(['name' => $name]);
        }
    }
}
