<?php

declare(strict_types=1);

use App\Filament\Pages\Support\FaqSupport;
use App\Models\FaqItem;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('syncItems creates new FAQ items with correct sort order', function (): void {
    FaqSupport::syncItems([
        ['id' => null, 'question' => 'Q1', 'answer' => 'A1'],
        ['id' => null, 'question' => 'Q2', 'answer' => 'A2'],
    ]);

    $items = FaqItem::query()->orderBy('sort_order')->get();

    expect($items)->toHaveCount(2)
        ->and($items[0]->question)->toBe('Q1')
        ->and($items[0]->sort_order)->toBe(0)
        ->and($items[1]->question)->toBe('Q2')
        ->and($items[1]->sort_order)->toBe(1);
});

test('syncItems updates existing and creates new items', function (): void {
    $existing = FaqItem::query()->create([
        'question' => 'Old Q',
        'answer' => 'Old A',
        'sort_order' => 0,
    ]);

    FaqSupport::syncItems([
        ['id' => $existing->id, 'question' => 'Updated Q', 'answer' => 'Updated A'],
        ['id' => null, 'question' => 'New Q', 'answer' => 'New A'],
    ]);

    $items = FaqItem::query()->orderBy('sort_order')->get();

    expect($items)->toHaveCount(2)
        ->and($items[0]->question)->toBe('Updated Q')
        ->and($items[0]->answer)->toBe('Updated A')
        ->and($items[0]->sort_order)->toBe(0)
        ->and($items[1]->question)->toBe('New Q')
        ->and($items[1]->sort_order)->toBe(1);
});

test('syncItems deletes orphaned items', function (): void {
    $keep = FaqItem::query()->create(['question' => 'Keep', 'answer' => 'A', 'sort_order' => 0]);
    $delete = FaqItem::query()->create(['question' => 'Delete', 'answer' => 'B', 'sort_order' => 1]);

    FaqSupport::syncItems([
        ['id' => $keep->id, 'question' => 'Keep', 'answer' => 'A'],
    ]);

    expect(FaqItem::query()->count())->toBe(1)
        ->and(FaqItem::query()->find($delete->id))->toBeNull();
});

test('syncItems handles empty repeater by deleting all', function (): void {
    FaqItem::query()->create(['question' => 'Q', 'answer' => 'A', 'sort_order' => 0]);

    FaqSupport::syncItems([]);

    expect(FaqItem::query()->count())->toBe(0);
});

test('syncItems reorders items based on array position', function (): void {
    $a = FaqItem::query()->create(['question' => 'A', 'answer' => 'A', 'sort_order' => 0]);
    $b = FaqItem::query()->create(['question' => 'B', 'answer' => 'B', 'sort_order' => 1]);

    // Swap order
    FaqSupport::syncItems([
        ['id' => $b->id, 'question' => 'B', 'answer' => 'B'],
        ['id' => $a->id, 'question' => 'A', 'answer' => 'A'],
    ]);

    expect($b->fresh()->sort_order)->toBe(0)
        ->and($a->fresh()->sort_order)->toBe(1);
});
