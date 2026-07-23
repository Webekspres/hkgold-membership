<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Address;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AddressSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        if (DB::table('villages')->count() === 0) {
            $this->call(LocationSeeder::class);
        }

        $villageId = DB::table('villages')->value('id');
        $postalCodeId = DB::table('postal_codes')->value('id');

        if ($villageId === null || $postalCodeId === null) {
            return;
        }

        $streets = [
            'Jl. Sudirman No. 45',
            'Jl. Gajah Mada No. 12',
            'Jl. Ahmad Yani No. 88',
            'Jl. Merdeka No. 3',
            'Jl. Diponegoro No. 67',
            'Jl. Kartini No. 21',
            'Jl. Pemuda No. 9',
            'Jl. Hasanuddin No. 102',
            'Komplek HK Gold Blok A No. 5',
            'Jl. Pahlawan No. 14',
        ];

        foreach ($streets as $street) {
            Address::query()->firstOrCreate(
                ['street' => $street],
                [
                    'id' => (string) Str::uuid(),
                    'village_id' => $villageId,
                    'postal_code_id' => $postalCodeId,
                ],
            );
        }
    }
}
