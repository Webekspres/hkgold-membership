<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Address;
use App\Models\Branch;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BranchSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        if (Address::query()->count() === 0) {
            $this->call(AddressSeeder::class);
        }

        $addressIds = Address::query()->pluck('id')->all();

        $branches = [
            ['HKG-PTK', 'HK Gold VIP Pontianak', -0.0263, 109.3425],
            ['HKG-SMG', 'HK Gold VIP Semarang', -6.9667, 110.4167],
            ['HKG-SLO', 'HK Gold VIP Solo', -7.5667, 110.8167],
            ['HKG-YGY', 'HK Gold VIP Yogyakarta', -7.7956, 110.3695],
            ['HKG-SBY', 'HK Gold VIP Surabaya', -7.2575, 112.7521],
            ['HKG-BDG', 'HK Gold VIP Bandung', -6.9175, 107.6191],
            ['HKG-JKT', 'HK Gold VIP Jakarta Pusat', -6.1751, 106.8650],
            ['HKG-MDN', 'HK Gold VIP Medan', 3.5952, 98.6722],
            ['HKG-PLB', 'HK Gold VIP Palembang', -2.9761, 104.7754],
            ['HKG-MKS', 'HK Gold VIP Makassar', -5.1477, 119.4327],
            ['HKG-BPN', 'HK Gold VIP Balikpapan', -1.2379, 116.8529],
            ['HKG-DPS', 'HK Gold VIP Denpasar', -8.6705, 115.2126],
        ];

        foreach ($branches as $index => [$code, $name, $lat, $lng]) {
            Branch::query()->firstOrCreate(
                ['code' => $code],
                [
                    'address_id' => $addressIds[$index % count($addressIds)],
                    'name' => $name,
                    'phone' => '021'.str_pad((string) ($index + 1), 7, '0', STR_PAD_LEFT),
                    'latitude' => $lat,
                    'longitude' => $lng,
                    'is_active' => true,
                    'open_time' => '08:00',
                    'close_time' => '17:00',
                ],
            );
        }
    }
}
