<?php

declare(strict_types=1);

namespace App\Services\Loyalty;

use App\Models\ConversionRule;
use App\Models\Member;
use App\Models\PointInjectionDetail;
use App\Models\TierMember;
use App\Models\TransactionType;

class RecalculateDetailPointsService
{
    /**
     * Recalculate and save calculated_points for a PointInjectionDetail record.
     *
     * Resolves the member from raw_member_number, looks up the ConversionRule
     * for their current tier and the given transaction type, and updates
     * the calculated_points accordingly. If no member or rule is found,
     * calculated_points is set to 0.
     */
    public function recalculate(PointInjectionDetail $detail): void
    {
        $member = Member::query()
            ->where('member_number', $detail->raw_member_number)
            ->first();

        if ($member === null) {
            $detail->calculated_points = 0;
            $detail->save();

            return;
        }

        $transactionType = TransactionType::query()->find($detail->transaction_type_id);

        if ($transactionType === null) {
            $detail->calculated_points = 0;
            $detail->save();

            return;
        }

        $tierMember = TierMember::query()
            ->where('tier_code', $member->current_tier)
            ->first();

        if ($tierMember === null) {
            $detail->calculated_points = 0;
            $detail->save();

            return;
        }

        $conversionRule = ConversionRule::query()
            ->where('transaction_type_id', $transactionType->id)
            ->where('tier_member_id', $tierMember->id)
            ->first();

        if ($conversionRule === null) {
            $detail->calculated_points = 0;
            $detail->save();

            return;
        }

        $conversionNominal = (string) $conversionRule->conversion_nominal;

        if (bccomp($conversionNominal, '0', 2) <= 0) {
            $detail->calculated_points = 0;
            $detail->save();

            return;
        }

        $detail->calculated_points = (int) bcdiv(
            (string) $detail->purchase_nominal,
            $conversionNominal,
            0
        );
        $detail->save();
    }
}
