<?php

declare(strict_types=1);

use App\Support\BulkInjectionDateParser;
use Illuminate\Support\Carbon;

it('parses bulk injection dates in d-m-Y format', function (): void {
    $parsed = BulkInjectionDateParser::parse('31-12-2026');

    expect($parsed)->not->toBeNull()
        ->and($parsed?->format('Y-m-d'))->toBe('2026-12-31');
});

it('parses bulk injection dates with legacy Y-m-d and d/m/Y fallbacks', function (): void {
    expect(BulkInjectionDateParser::parse('2026-06-15')?->format('Y-m-d'))->toBe('2026-06-15')
        ->and(BulkInjectionDateParser::parse('15/06/2026')?->format('Y-m-d'))->toBe('2026-06-15');
});

it('rejects invalid bulk injection date strings', function (): void {
    expect(BulkInjectionDateParser::parse('12/31/2026'))->toBeNull()
        ->and(BulkInjectionDateParser::parse('invalid'))->toBeNull();
});

it('formats template dates as d-m-Y', function (): void {
    expect(BulkInjectionDateParser::formatForTemplate(Carbon::parse('2026-12-31')))
        ->toBe('31-12-2026');
});
