<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call([
            LocationSeeder::class,
            AddressSeeder::class,
            // Core loyalty domain
            LoyaltyConfigSeeder::class,
            CategoryRewardSeeder::class,
            RewardSeeder::class,
            MediaSeeder::class,
            ShieldRolesSeeder::class,
            UserSeeder::class,
            BranchSeeder::class,
            StaffSeeder::class,
            MemberSeeder::class,
            BranchRewardStockSeeder::class,
            MemberAnomalySeeder::class,
            FraudSuspectSeeder::class,
            PhoneApprovalSeeder::class,
            PointInjectionBatchSeeder::class,
            PointMutationSeeder::class,
            PointAnnualArchiveSeeder::class,
            RedeemInvoiceSeeder::class,
            ContentSeeder::class,
        ]);
    }
}
