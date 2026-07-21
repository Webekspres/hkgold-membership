<?php

declare(strict_types=1);

namespace App\Filament\Resources\TierMembers\Support;

use App\Enums\TierStatus;
use App\Models\TierMember;

class TierSupport
{
    private static function meta(TierStatus $tier): array
    {
        return match ($tier) {
            TierStatus::Silver => ['label' => 'Silver',   'color' => 'gray',    'order' => 1],
            TierStatus::Gold => ['label' => 'Gold',     'color' => 'warning', 'order' => 2],
            TierStatus::Platinum => ['label' => 'Platinum', 'color' => 'info',    'order' => 3],
            TierStatus::Elite => ['label' => 'Elite', 'color' => 'sky',     'order' => 4],
        };
    }

    public static function label(TierStatus $tier): string
    {
        return self::meta($tier)['label'];
    }

    public static function color(TierStatus $tier): string
    {
        return self::meta($tier)['color'];
    }

    public static function order(TierStatus $tier): int
    {
        return self::meta($tier)['order'];
    }

    /**
     * Validate that the given range [minPoints, maxPoints] does not overlap
     * with any other tier's range, excluding the current record.
     */
    public static function noOverlapRule(?TierMember $record, int $minPoints): \Closure
    {
        return function (string $attribute, mixed $value, \Closure $fail) use ($record, $minPoints): void {
            $maxPoints = (int) $value;

            if ($maxPoints <= $minPoints) {
                return;
            }

            $overlaps = TierMember::query()
                ->when($record !== null, fn ($q) => $q->where('id', '!=', $record->id))
                ->where(function ($q) use ($minPoints, $maxPoints): void {
                    $q->where('min_points', '<=', $maxPoints)
                        ->where('max_points', '>=', $minPoints);
                })
                ->exists();

            if ($overlaps) {
                $fail('Range poin ini overlap dengan tier lain.');
            }
        };
    }
}
