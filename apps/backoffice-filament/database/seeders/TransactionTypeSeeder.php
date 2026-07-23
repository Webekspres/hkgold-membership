<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TransactionTypeSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $types = [
            ['PERHIASAN', 'Perhiasan'],
            ['BERLIAN',   'Berlian'],
            ['REDEEM',    'Redeem Poin'],
        ];

        foreach ($types as [$typeKey, $displayName]) {
            DB::table('transaction_types')->updateOrInsert(
                ['type_key' => $typeKey],
                [
                    'display_name' => $displayName,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            );
        }
    }
}
