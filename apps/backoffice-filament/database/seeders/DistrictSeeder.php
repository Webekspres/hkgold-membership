<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\District;
use App\Models\Regency;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DistrictSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $regencies = Regency::query()->pluck('id', 'name');

        if ($regencies->isEmpty()) {
            $this->call(RegencySeeder::class);
            $regencies = Regency::query()->pluck('id', 'name');
        }

        $districts = [
            ['name' => 'Semarang Tengah', 'regency' => 'Kota Semarang'],
            ['name' => 'Laweyan', 'regency' => 'Kota Solo'],
            ['name' => 'Pontianak Kota', 'regency' => 'Kota Pontianak'],
            ['name' => 'Gondokusuman', 'regency' => 'Kota Yogyakarta'],
            ['name' => 'Genteng', 'regency' => 'Kota Surabaya'],
            ['name' => 'Coblong', 'regency' => 'Kota Bandung'],
            ['name' => 'Kebayoran Baru', 'regency' => 'Kota Jakarta Selatan'],
            ['name' => 'Demak', 'regency' => 'Kabupaten Demak'],
            ['name' => 'Depok', 'regency' => 'Kabupaten Sleman'],
            ['name' => 'Klojen', 'regency' => 'Kota Malang'],
        ];

        foreach ($districts as $district) {
            District::query()->firstOrCreate(
                ['name' => $district['name'], 'regency_id' => $regencies[$district['regency']]],
            );
        }
    }
}
