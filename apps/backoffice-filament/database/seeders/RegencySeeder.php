<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Province;
use App\Models\Regency;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RegencySeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $provinces = Province::query()->pluck('id', 'name');

        if ($provinces->isEmpty()) {
            $this->call(ProvinceSeeder::class);
            $provinces = Province::query()->pluck('id', 'name');
        }

        $regencies = [
            ['name' => 'Kota Semarang', 'province' => 'Jawa Tengah'],
            ['name' => 'Kota Solo', 'province' => 'Jawa Tengah'],
            ['name' => 'Kota Pontianak', 'province' => 'Kalimantan Barat'],
            ['name' => 'Kota Yogyakarta', 'province' => 'DI Yogyakarta'],
            ['name' => 'Kota Surabaya', 'province' => 'Jawa Timur'],
            ['name' => 'Kota Bandung', 'province' => 'Jawa Barat'],
            ['name' => 'Kota Jakarta Selatan', 'province' => 'DKI Jakarta'],
            ['name' => 'Kabupaten Demak', 'province' => 'Jawa Tengah'],
            ['name' => 'Kabupaten Sleman', 'province' => 'DI Yogyakarta'],
            ['name' => 'Kota Malang', 'province' => 'Jawa Timur'],
        ];

        foreach ($regencies as $regency) {
            Regency::query()->firstOrCreate(
                ['name' => $regency['name']],
                ['province_id' => $provinces[$regency['province']]],
            );
        }
    }
}
