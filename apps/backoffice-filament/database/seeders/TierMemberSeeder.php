<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\TierStatus;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TierMemberSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $tiers = [
            [TierStatus::Silver, 0, 1000],
            [TierStatus::Gold, 1001, 2000],
            [TierStatus::Platinum, 2001, 4000],
            [TierStatus::Sapphire, 4001, 99999],
        ];

        foreach ($tiers as [$tier, $minPoints, $maxPoints]) {
            DB::table('tier_members')->updateOrInsert(
                ['tier_code' => $tier->value],
                [
                    'min_points' => $minPoints,
                    'max_points' => $maxPoints,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            );
        }
    }
}
