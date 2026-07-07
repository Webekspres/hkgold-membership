<?php

declare(strict_types=1);

namespace App\Support;

use DateTimeInterface;
use Illuminate\Support\Carbon;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class BulkInjectionDateParser
{
    /** @var list<string> */
    private const SUPPORTED_FORMATS = [
        'd-m-Y',
        'Y-m-d',
        'd/m/Y',
    ];

    public static function parse(mixed $value): ?Carbon
    {
        if ($value instanceof DateTimeInterface) {
            return Carbon::instance($value)->startOfDay();
        }

        if (is_numeric($value)) {
            try {
                return Carbon::instance(ExcelDate::excelToDateTimeObject((float) $value))->startOfDay();
            } catch (\Throwable) {
                return null;
            }
        }

        if (blank($value)) {
            return null;
        }

        $string = trim((string) $value);

        foreach (self::SUPPORTED_FORMATS as $format) {
            try {
                $parsed = Carbon::createFromFormat($format, $string);

                if ($parsed->format($format) === $string) {
                    return $parsed->startOfDay();
                }
            } catch (\Throwable) {
                continue;
            }
        }

        return null;
    }

    public static function formatForTemplate(Carbon $date): string
    {
        return $date->format('d-m-Y');
    }
}
