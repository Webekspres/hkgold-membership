<?php

declare(strict_types=1);

namespace App\Filament\Support;

use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;

class IndonesianDateTimeFormatter
{
    public static function tableDate(mixed $value): ?string
    {
        $date = self::parse($value);

        if ($date === null) {
            return null;
        }

        return $date
            ->timezone(config('app.timezone'))
            ->locale('id')
            ->translatedFormat('l, j M Y');
    }

    public static function tableDateTooltip(mixed $value): ?string
    {
        $date = self::parse($value);

        if ($date === null) {
            return null;
        }

        return $date
            ->timezone(config('app.timezone'))
            ->locale('id')
            ->translatedFormat('H:i l, j F Y');
    }

    private static function parse(mixed $value): ?CarbonInterface
    {
        if ($value === null || $value === '') {
            return null;
        }

        if ($value instanceof CarbonInterface) {
            return $value;
        }

        return Carbon::parse($value);
    }
}
