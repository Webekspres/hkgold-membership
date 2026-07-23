<?php

declare(strict_types=1);

namespace App\Filament\Resources\PointMutations\Support;

use App\Models\Member;
use App\Models\PointMutation;

class PointMutationSupport
{
    /**
     * @return array{formatted: string, color: string}
     */
    public static function formatPointsDelta(PointMutation $record): array
    {
        if ($record->points_redeemed > 0) {
            return [
                'formatted' => '-'.self::formatNumber($record->points_redeemed),
                'color' => 'danger',
            ];
        }

        return [
            'formatted' => '+'.self::formatNumber($record->points_issued),
            'color' => 'success',
        ];
    }

    public static function averagePointsPerMember(): float
    {
        return (float) Member::query()
            ->where('is_suspended', false)
            ->avg('point_balance');
    }

    public static function pointsIssuedLastSevenDays(): int
    {
        return (int) PointMutation::query()
            ->where('transaction_date', '>=', now()->subDays(7))
            ->sum('points_issued');
    }

    public static function pointsRedeemedLastSevenDays(): int
    {
        return (int) PointMutation::query()
            ->where('transaction_date', '>=', now()->subDays(7))
            ->sum('points_redeemed');
    }

    public static function formatNumber(int|float $value): string
    {
        return number_format($value, 0, '.', ',');
    }
}
