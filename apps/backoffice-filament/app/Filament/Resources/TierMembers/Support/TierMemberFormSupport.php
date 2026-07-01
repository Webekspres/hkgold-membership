<?php

declare(strict_types=1);

namespace App\Filament\Resources\TierMembers\Support;

use App\Models\ConversionRule;
use App\Models\TierMember;
use App\Models\TransactionType;

class TierMemberFormSupport
{
    /**
     * Build a form key from a transaction type key.
     * e.g. 'PERHIASAN' → 'conversion_perhiasan'
     */
    public static function conversionFieldKey(string $typeKey): string
    {
        return 'conversion_' . strtolower($typeKey);
    }

    /**
     * Pre-populate form data with conversion rule values so the edit modal
     * shows existing nominal amounts alongside min/max points.
     */
    public static function fillFormData(TierMember $record): array
    {
        $data = [
            'min_points' => $record->min_points,
            'max_points' => $record->max_points,
        ];

        $record->loadMissing('conversionRules.transactionType');

        foreach ($record->conversionRules as $rule) {
            $key          = self::conversionFieldKey($rule->transactionType->type_key);
            $data[$key]   = (string) $rule->conversion_nominal;
        }

        return $data;
    }

    /**
     * Persist the tier member's min/max points and upsert each conversion rule
     * from the virtual form fields.
     */
    public static function saveWithConversions(TierMember $record, array $data): TierMember
    {
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
                    'tier_member_id'      => $record->id,
                ],
                [
                    'conversion_nominal' => $data[$key],
                ],
            );
        }

        return $record;
    }
}
