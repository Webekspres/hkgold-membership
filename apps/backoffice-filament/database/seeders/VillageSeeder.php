<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\District;
use App\Models\Village;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class VillageSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $districts = District::query()->pluck('id', 'name');

        if ($districts->isEmpty()) {
            $this->call(DistrictSeeder::class);
            $districts = District::query()->pluck('id', 'name');
        }

        $villages = [
            ['name' => 'Kelurahan Pandansari', 'district' => 'Semarang Tengah'],
            ['name' => 'Kelurahan Laweyan', 'district' => 'Laweyan'],
            ['name' => 'Kelurahan Benua Melayu Laut', 'district' => 'Pontianak Kota'],
            ['name' => 'Kelurahan Kotabaru', 'district' => 'Gondokusuman'],
            ['name' => 'Kelurahan Genteng', 'district' => 'Genteng'],
            ['name' => 'Kelurahan Dago', 'district' => 'Coblong'],
            ['name' => 'Kelurahan Senayan', 'district' => 'Kebayoran Baru'],
            ['name' => 'Desa Wonorejo', 'district' => 'Demak'],
            ['name' => 'Kelurahan Catur Tunggal', 'district' => 'Depok'],
            ['name' => 'Kelurahan Klojen', 'district' => 'Klojen'],
        ];

        foreach ($villages as $village) {
            Village::query()->firstOrCreate(
                ['name' => $village['name'], 'district_id' => $districts[$village['district']]],
            );
        }
    }
}
