<?php

declare(strict_types=1);

namespace App\Services\Loyalty;

use App\Enums\TierStatus;
use App\Exceptions\Loyalty\ManualPointInjectionException;
use App\Filament\Resources\TierMembers\Support\TierSupport;
use App\Models\ConversionRule;
use App\Models\Member;
use App\Models\TierMember;
use App\Models\TransactionType;

class PointCalculationService
{
    public function calculateIssuedPoints(string $purchaseNominal, string $conversionNominal): int
    {
        if (bccomp($conversionNominal, '0', 2) <= 0) {
            return 0;
        }

        return (int) bcdiv($purchaseNominal, $conversionNominal, 0);
    }

    public function resolveEligibleTierUpgrade(TierStatus $currentTier, int $newBalance): TierStatus
    {
        $eligibleTier = TierMember::query()
            ->where('min_points', '<=', $newBalance)
            ->where('max_points', '>=', $newBalance)
            ->first();

        if ($eligibleTier === null) {
            $eligibleTier = TierMember::query()
                ->where('min_points', '<=', $newBalance)
                ->orderByDesc('min_points')
                ->first();
        }

        if ($eligibleTier === null) {
            return $currentTier;
        }

        $eligibleStatus = $eligibleTier->tier_code;

        if (TierSupport::order($eligibleStatus) > TierSupport::order($currentTier)) {
            return $eligibleStatus;
        }

        return $currentTier;
    }

    /**
     * @return array{points_issued: int, conversion_nominal: string}
     */
    public function preview(Member $member, TransactionType $transactionType, string $purchaseNominal): array
    {
        $conversionRule = $this->resolveConversionRule($member, $transactionType);
        $conversionNominal = (string) $conversionRule->conversion_nominal;

        return [
            'points_issued' => $this->calculateIssuedPoints($purchaseNominal, $conversionNominal),
            'conversion_nominal' => $conversionNominal,
        ];
    }

    public function resolveConversionRule(Member $member, TransactionType $transactionType): ConversionRule
    {
        $tierMember = TierMember::query()
            ->where('tier_code', $member->current_tier)
            ->first();

        if ($tierMember === null) {
            throw ManualPointInjectionException::conversionRuleNotFound();
        }

        $conversionRule = ConversionRule::query()
            ->where('transaction_type_id', $transactionType->id)
            ->where('tier_member_id', $tierMember->id)
            ->first();

        if ($conversionRule === null) {
            throw ManualPointInjectionException::conversionRuleNotFound();
        }

        return $conversionRule;
    }
}
