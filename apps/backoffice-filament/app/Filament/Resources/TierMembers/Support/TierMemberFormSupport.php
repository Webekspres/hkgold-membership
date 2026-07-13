<?php

declare(strict_types=1);

namespace App\Filament\Resources\TierMembers\Support;

use App\Enums\ActivityLogAction;
use App\Models\ConversionRule;
use App\Models\TierBenefit;
use App\Models\TierMember;
use App\Models\TransactionType;
use App\Services\ActivityLog\ActivityLogger;
use App\Support\ActivityLog\ActivityLogSanitizer;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TierMemberFormSupport
{
    /**
     * Build a form key from a transaction type key.
     * e.g. 'PERHIASAN' → 'conversion_perhiasan'
     */
    public static function conversionFieldKey(string $typeKey): string
    {
        return 'conversion_'.strtolower($typeKey);
    }

    /**
     * Pre-populate form data with conversion rule values so the edit modal
     * shows existing nominal amounts alongside min/max points.
     *
     * @return array<string, mixed>
     */
    public static function fillFormData(TierMember $record): array
    {
        $data = [
            'min_points' => $record->min_points,
            'max_points' => $record->max_points,
        ];

        $record->loadMissing(['conversionRules.transactionType', 'tierBenefits']);

        foreach ($record->conversionRules as $rule) {
            $key = self::conversionFieldKey($rule->transactionType->type_key);
            $data[$key] = (string) $rule->conversion_nominal;
        }

        $data['benefits'] = $record->tierBenefits
            ->map(fn (TierBenefit $benefit): array => [
                'id' => $benefit->id,
                'title' => $benefit->title,
                'description' => $benefit->description,
            ])
            ->values()
            ->all();

        return $data;
    }

    /**
     * Persist the tier member's min/max points, upsert conversion rules,
     * and sync benefits from the repeater.
     *
     * @param  array<string, mixed>  $data
     */
    public static function saveWithConversions(TierMember $record, array $data): TierMember
    {
        $before = ActivityLogSanitizer::extract($record);

        DB::transaction(function () use ($record, $data): void {
            $record->update([
                'min_points' => $data['min_points'],
                'max_points' => $data['max_points'],
            ]);

            $transactionTypes = TransactionType::all();

            foreach ($transactionTypes as $type) {
                $key = self::conversionFieldKey($type->type_key);

                if (! array_key_exists($key, $data) || $data[$key] === null || $data[$key] === '') {
                    continue;
                }

                ConversionRule::updateOrCreate(
                    [
                        'transaction_type_id' => $type->id,
                        'tier_member_id' => $record->id,
                    ],
                    [
                        'conversion_nominal' => $data[$key],
                    ],
                );
            }

            self::syncBenefits($record, $data['benefits'] ?? []);
        });

        $record->refresh();

        app(ActivityLogger::class)->log(
            action: ActivityLogAction::TierConfigUpdated,
            description: 'Memperbarui konfigurasi tier member',
            auditable: $record,
            ipAddress: (string) request()->ip(),
            before: $before,
            after: ActivityLogSanitizer::extract($record),
            actor: Auth::user(),
        );

        return $record;
    }

    /**
     * @param  array<int, array<string, mixed>>  $benefits
     */
    private static function syncBenefits(TierMember $record, array $benefits): void
    {
        $keptIds = [];

        foreach (array_values($benefits) as $index => $benefit) {
            $title = trim((string) ($benefit['title'] ?? ''));
            $description = trim((string) ($benefit['description'] ?? ''));

            if ($title === '' || $description === '') {
                continue;
            }

            $existingId = filled($benefit['id'] ?? null) ? (string) $benefit['id'] : null;

            if ($existingId !== null) {
                $existing = TierBenefit::query()
                    ->whereKey($existingId)
                    ->where('tier_member_id', $record->id)
                    ->first();

                if ($existing !== null) {
                    $existing->update([
                        'title' => $title,
                        'description' => $description,
                        'sort_order' => $index,
                        'is_active' => true,
                    ]);

                    $keptIds[] = $existing->id;

                    continue;
                }
            }

            $created = TierBenefit::query()->create([
                'tier_member_id' => $record->id,
                'title' => $title,
                'description' => $description,
                'sort_order' => $index,
                'is_active' => true,
            ]);

            $keptIds[] = $created->id;
        }

        TierBenefit::query()
            ->where('tier_member_id', $record->id)
            ->when($keptIds !== [], fn ($query) => $query->whereNotIn('id', $keptIds))
            ->delete();
    }
}
