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
            [TierStatus::Silver, 0, 49_999],
            [TierStatus::Gold, 50_000, 199_999],
            [TierStatus::Platinum, 200_000, 499_999],
            [TierStatus::Sapphire, 500_000, 9_999_999],
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
