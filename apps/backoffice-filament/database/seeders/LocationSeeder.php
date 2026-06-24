<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class LocationSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        if (DB::table('provinces')->count() > 0) {
            $this->command?->info('Location data already seeded, skipping.');

            return;
        }

        $provinceId = (string) Str::uuid();
        $regencyId = (string) Str::uuid();
        $districtId = (string) Str::uuid();
        $villageId = (string) Str::uuid();
        $postalCodeId = (string) Str::uuid();

        DB::table('provinces')->insert([
            'id' => $provinceId,
            'name' => 'Kalimantan Barat',
        ]);

        DB::table('regencies')->insert([
            'id' => $regencyId,
            'province_id' => $provinceId,
            'name' => 'Kota Pontianak',
        ]);

        DB::table('districts')->insert([
            'id' => $districtId,
            'regency_id' => $regencyId,
            'name' => 'Pontianak Kota',
        ]);

        DB::table('villages')->insert([
            'id' => $villageId,
            'district_id' => $districtId,
            'name' => 'Darat Sekip',
        ]);

        DB::table('postal_codes')->insert([
            'id' => $postalCodeId,
            'code' => '78113',
        ]);

        $this->command?->info('Minimal location data seeded.');
    }
}
