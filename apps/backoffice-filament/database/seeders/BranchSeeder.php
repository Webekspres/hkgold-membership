<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Address;
use App\Models\Branch;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class BranchSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // [code, name, street, cityLabel, villageId, postalCodeId, latitude, longitude]
        // Koordinat = Nominatim OSM untuk jalan yang disebut (bukan pusat kota generik).
        $branches = [
            ['HK01', 'HK Gold VIP Pontianak', 'Jl. Gajah Mada No. 1', 'Pontianak', 38766, 2939, -0.0379147, 109.3430635],
            ['HK02', 'HK Gold VIP Semarang', 'Jl. Pemuda No. 10', 'Semarang', 24613, 627, -6.9803310, 110.4135517],
            ['HK03', 'HK Gold VIP Solo', 'Jl. Slamet Riyadi No. 5', 'Solo', 25334, 814, -7.5676627, 110.8134472],
            ['HK04', 'HK Gold VIP Yogyakarta', 'Jl. Malioboro No. 20', 'Yogyakarta', 5473, 8017, -7.7952921, 110.3657274],
            ['HK05', 'HK Gold VIP Surabaya', 'Jl. Tunjungan No. 8', 'Surabaya', 36423, 2655, -7.2584753, 112.7383666],
            ['HK06', 'HK Gold VIP Bandung', 'Jl. Asia Afrika No. 15', 'Bandung', 8810, 8765, -6.9209426, 107.6052769],
            ['HK07', 'HK Gold VIP Jakarta', 'Jl. Jenderal Sudirman No. 45', 'Jakarta', 5734, 8162, -6.2230433, 106.8078192],
            ['HK08', 'HK Gold VIP Medan', 'Jl. Gatot Subroto No. 3', 'Medan', 85876, 7409, 3.5916493, 98.6641661],
            ['HK09', 'HK Gold VIP Palembang', 'Jl. Jenderal Sudirman No. 12', 'Palembang', 83116, 7092, -2.9665233, 104.7483479],
            ['HK10', 'HK Gold VIP Makassar', 'Jl. Pengayoman No. 7', 'Makassar', 71647, 5584, -5.1602447, 119.4483609],
            ['HK11', 'HK Gold VIP Balikpapan', 'Jl. Jenderal Sudirman No. 2', 'Balikpapan', 43440, 3472, -1.2741808, 116.8733839],
            ['HK12', 'HK Gold VIP Denpasar', 'Jl. Gajah Mada No. 88', 'Denpasar', 304, 37, -8.6552271, 115.2115353],
        ];

        foreach ($branches as $index => [$code, $name, $street, $cityLabel, $villageId, $postalCodeId, $latitude, $longitude]) {
            $displayAddress = "{$street}, {$cityLabel}";
            $locationUrl = sprintf('https://maps.google.com/?q=%.7f,%.7f', $latitude, $longitude);

            $address = Address::query()->firstOrNew([
                'village_id' => $villageId,
                'street' => $street,
            ]);

            if (! $address->exists) {
                $address->id = (string) Str::uuid();
            }

            $address->postal_code_id = $postalCodeId;
            $address->save();

            Branch::query()->updateOrCreate(
                ['branch_code' => $code],
                [
                    'address_id' => $address->id,
                    'name' => $name,
                    'address' => $displayAddress,
                    'location_url' => $locationUrl,
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                    'phone' => '021'.str_pad((string) ($index + 1), 7, '0', STR_PAD_LEFT),
                    'is_online_warehouse' => false,
                ],
            );
        }
    }
}
