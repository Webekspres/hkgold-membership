<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Member;
use App\Models\PointAnnualArchive;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PointAnnualArchiveSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        if (Member::query()->count() === 0) {
            $this->call(MemberSeeder::class);
        }

        $members = Member::query()->limit(10)->get();

        foreach ($members as $member) {
            PointAnnualArchive::query()->firstOrCreate(
                [
                    'member_id' => $member->id,
                    'archive_year' => (int) date('Y') - 1,
                ],
                [
                    'frozen_points_total' => $member->point_balance,
                    'last_tier_position' => $member->current_tier->value,
                ],
            );
        }
    }
}
