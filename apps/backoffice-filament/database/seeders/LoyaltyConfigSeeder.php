<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\TierStatus;
use App\Models\LoyaltyConfig;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LoyaltyConfigSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $configs = [
            [TierStatus::Silver, 10000],
            [TierStatus::Gold, 12500],
            [TierStatus::Platinum, 15000],
            [TierStatus::Sapphire, 20000],
        ];

        foreach ($configs as [$tier, $multiplier]) {
            LoyaltyConfig::query()->updateOrCreate(
                ['tier' => $tier],
                ['multiplier_cost' => $multiplier],
            );
        }
    }
}
