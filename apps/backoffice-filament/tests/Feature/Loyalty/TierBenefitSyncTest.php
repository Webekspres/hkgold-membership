<?php

declare(strict_types=1);

use App\Enums\TierStatus;
use App\Filament\Resources\TierMembers\Support\TierMemberFormSupport;
use App\Jobs\PersistActivityLogJob;
use App\Models\TierBenefit;
use App\Models\TierMember;
use App\Models\TransactionType;
use App\Models\User;
use Database\Seeders\TierMemberSeeder;
use Database\Seeders\TransactionTypeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    Queue::fake([PersistActivityLogJob::class]);
    (new TransactionTypeSeeder)->run();
    (new TierMemberSeeder)->run();
});

test('fillFormData menyertakan benefits dari tier', function (): void {
    $tier = TierMember::query()->where('tier_code', TierStatus::Gold)->firstOrFail();

    $data = TierMemberFormSupport::fillFormData($tier);

    expect($data)->toHaveKey('benefits')
        ->and($data['benefits'])->not->toBeEmpty()
        ->and($data['benefits'][0])->toHaveKeys(['id', 'title', 'description']);
});

test('saveWithConversions sync menambah memperbarui dan menghapus benefits', function (): void {
    $admin = User::factory()->administrator()->create();
    $this->actingAs($admin);

    $tier = TierMember::query()->where('tier_code', TierStatus::Silver)->firstOrFail();
    $existing = $tier->tierBenefits()->firstOrFail();

    $conversionData = [];
    foreach (TransactionType::query()->orderBy('id')->get() as $type) {
        $conversionData[TierMemberFormSupport::conversionFieldKey($type->type_key)] = '100000';
    }

    TierMemberFormSupport::saveWithConversions($tier, [
        'min_points' => $tier->min_points,
        'max_points' => $tier->max_points,
        ...$conversionData,
        'benefits' => [
            [
                'id' => $existing->id,
                'title' => 'Benefit diperbarui',
                'description' => 'Keterangan diperbarui',
            ],
            [
                'id' => null,
                'title' => 'Benefit baru',
                'description' => 'Keterangan baru',
            ],
        ],
    ]);

    $tier->refresh();
    $benefits = $tier->tierBenefits()->orderBy('sort_order')->get();

    expect($benefits)->toHaveCount(2)
        ->and($benefits[0]->title)->toBe('Benefit diperbarui')
        ->and($benefits[0]->description)->toBe('Keterangan diperbarui')
        ->and($benefits[0]->sort_order)->toBe(0)
        ->and($benefits[1]->title)->toBe('Benefit baru')
        ->and($benefits[1]->sort_order)->toBe(1)
        ->and(TierBenefit::query()->where('tier_member_id', $tier->id)->count())->toBe(2);
});

test('saveWithConversions menghapus semua benefits jika repeater kosong', function (): void {
    $admin = User::factory()->administrator()->create();
    $this->actingAs($admin);

    $tier = TierMember::query()->where('tier_code', TierStatus::Platinum)->firstOrFail();
    expect($tier->tierBenefits()->count())->toBeGreaterThan(0);

    $conversionData = [];
    foreach (TransactionType::query()->orderBy('id')->get() as $type) {
        $conversionData[TierMemberFormSupport::conversionFieldKey($type->type_key)] = '100000';
    }

    TierMemberFormSupport::saveWithConversions($tier, [
        'min_points' => $tier->min_points,
        'max_points' => $tier->max_points,
        ...$conversionData,
        'benefits' => [],
    ]);

    expect($tier->tierBenefits()->count())->toBe(0);
});
