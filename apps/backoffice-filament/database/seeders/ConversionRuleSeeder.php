<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\ConversionRule;
use App\Models\TierMember;
use App\Models\TransactionType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ConversionRuleSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $transactionTypes = TransactionType::query()->get();
        $tierMembers = TierMember::query()->get();

        if ($transactionTypes->isEmpty() || $tierMembers->isEmpty()) {
            return;
        }

        foreach ($tierMembers as $tierMember) {
            foreach ($transactionTypes as $transactionType) {
                $baseNominal = $transactionType->type_key === 'BERLIAN' ? '150000.00' : '100000.00';

                ConversionRule::query()->updateOrCreate(
                    [
                        'transaction_type_id' => $transactionType->id,
                        'tier_member_id' => $tierMember->id,
                    ],
                    [
                        'conversion_nominal' => $baseNominal,
                    ],
                );
            }
        }
    }
}
