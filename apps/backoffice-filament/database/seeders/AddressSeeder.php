<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Address;
use App\Models\PostalCode;
use App\Models\Village;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AddressSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        if (Village::query()->count() === 0) {
            $this->call(VillageSeeder::class);
        }

        if (PostalCode::query()->count() === 0) {
            $this->call(PostalCodeSeeder::class);
        }

        $villages = Village::query()->pluck('id')->all();
        $postalCodes = PostalCode::query()->pluck('id')->all();

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

        foreach ($streets as $index => $street) {
            Address::query()->firstOrCreate(
                ['street' => $street],
                [
                    'village_id' => $villages[$index % count($villages)],
                    'postal_code_id' => $postalCodes[$index % count($postalCodes)],
                ],
            );
        }
    }
}
