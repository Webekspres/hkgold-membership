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
            ['HK01', 'HK Gold VIP Pontianak', 'Jl. Gajah Mada No. 1, Pontianak'],
            ['HK02', 'HK Gold VIP Semarang', 'Jl. Pemuda No. 10, Semarang'],
            ['HK03', 'HK Gold VIP Solo', 'Jl. Slamet Riyadi No. 5, Solo'],
            ['HK04', 'HK Gold VIP Yogyakarta', 'Jl. Malioboro No. 20, Yogyakarta'],
            ['HK05', 'HK Gold VIP Surabaya', 'Jl. Tunjungan No. 8, Surabaya'],
            ['HK06', 'HK Gold VIP Bandung', 'Jl. Asia Afrika No. 15, Bandung'],
            ['HK07', 'HK Gold VIP Jakarta', 'Jl. Sudirman No. 45, Jakarta'],
            ['HK08', 'HK Gold VIP Medan', 'Jl. Gatot Subroto No. 3, Medan'],
            ['HK09', 'HK Gold VIP Palembang', 'Jl. Sudirman No. 12, Palembang'],
            ['HK10', 'HK Gold VIP Makassar', 'Jl. Pengayoman No. 7, Makassar'],
            ['HK11', 'HK Gold VIP Balikpapan', 'Jl. Jenderal Sudirman No. 2, Balikpapan'],
            ['HK12', 'HK Gold VIP Denpasar', 'Jl. Gajah Mada No. 88, Denpasar'],
        ];

        foreach ($branches as $index => [$code, $name, $address]) {
            Branch::query()->firstOrCreate(
                ['branch_code' => $code],
                [
                    'address_id' => $addressIds[$index % count($addressIds)],
                    'name' => $name,
                    'address' => $address,
                    'phone' => '021'.str_pad((string) ($index + 1), 7, '0', STR_PAD_LEFT),
                    'is_online_warehouse' => false,
                ],
            );
        }
    }
}
