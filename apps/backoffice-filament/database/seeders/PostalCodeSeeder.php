<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\PostalCode;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PostalCodeSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $codes = [
            '50132',
            '57141',
            '78116',
            '55224',
            '60275',
            '40131',
            '12190',
            '59511',
            '55281',
            '65111',
        ];

        foreach ($codes as $code) {
            PostalCode::query()->firstOrCreate(['code' => $code]);
        }
    }
}
