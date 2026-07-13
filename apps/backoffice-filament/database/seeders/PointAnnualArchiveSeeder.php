<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Member;
use App\Models\PointAnnualArchive;
use App\Models\PointAnnualArchivePeriod;
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

        $currentYear = (int) date('Y');
        // Jangan seed tahun target arsip (tahun sebelumnya) agar tombol "Arsipkan Poin" tetap bisa diuji.
        $years = [$currentYear - 3, $currentYear - 2];

        $members = Member::query()->limit(12)->get();
        if ($members->count() === 0) {
            return;
        }

        foreach ($years as $index => $year) {
            $period = PointAnnualArchivePeriod::query()->firstOrCreate(
                ['archive_year' => $year],
                [
                    'name' => 'Arsip Poin '.$year,
                    'total_members' => 0,
                    'frozen_points_total' => 0,
                    'earned_points_total' => 0,
                    'redeemed_points_total' => 0,
                    'archived_at' => now()->subYears($currentYear - $year)->subDays(rand(1, 10)),
                ]
            );

            // Slicing 4 members for each period
            $subset = $members->slice($index * 4, 4);

            $totalMembers = 0;
            $totalFrozen = 0;
            $totalEarned = 0;
            $totalRedeemed = 0;

            foreach ($subset as $member) {
                // Determine mock values for this year
                $baseMultiplier = ($index + 1) * 200;
                $frozen = (int) ($member->point_balance > 0 ? $member->point_balance : $baseMultiplier);
                $highest = max($member->highest_point, $frozen);
                $redeemed = (int) ($frozen * (0.35 + ($index * 0.05)));
                $earned = $frozen + $redeemed + (int) ($frozen * 0.2);

                $archive = PointAnnualArchive::query()->firstOrCreate(
                    [
                        'period_id' => $period->id,
                        'member_id' => $member->id,
                    ],
                    [
                        'frozen_points_total' => $frozen,
                        'highest_point' => $highest,
                        'last_tier_position' => $member->current_tier->value,
                        'frozen_at' => now()->subYears($currentYear - $year),
                    ]
                );

                if ($archive->wasRecentlyCreated) {
                    $totalMembers++;
                    $totalFrozen += $frozen;
                    $totalEarned += $earned;
                    $totalRedeemed += $redeemed;
                }
            }

            if ($totalMembers > 0) {
                $period->increment('total_members', $totalMembers);
                $period->increment('frozen_points_total', $totalFrozen);
                $period->increment('earned_points_total', $totalEarned);
                $period->increment('redeemed_points_total', $totalRedeemed);
            }
        }
    }
}
