<?php

declare(strict_types=1);

namespace App\Data\Loyalty;

use App\Enums\TierStatus;
use Carbon\CarbonInterface;

readonly class ManualPointInjectionResult
{
    public function __construct(
        public string $mutationId,
        public string $memberId,
        public string $memberNumber,
        public string $memberName,
        public ?int $branchId,
        public int $transactionTypeId,
        public string $transactionType,
        public string $purchaseNominal,
        public int $pointsIssued,
        public int $previousBalance,
        public int $newBalance,
        public int $balanceSnapshot,
        public TierStatus $previousTier,
        public TierStatus $newTier,
        public bool $tierUpgraded,
        public ?string $referenceId,
        public CarbonInterface $transactionDate,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'success' => true,
            'data' => [
                'mutation_id' => $this->mutationId,
                'member_id' => $this->memberId,
                'member_number' => $this->memberNumber,
                'member_name' => $this->memberName,
                'branch_id' => $this->branchId,
                'transaction_type_id' => $this->transactionTypeId,
                'transaction_type' => $this->transactionType,
                'purchase_nominal' => $this->purchaseNominal,
                'points_issued' => $this->pointsIssued,
                'previous_balance' => $this->previousBalance,
                'new_balance' => $this->newBalance,
                'balance_snapshot' => $this->balanceSnapshot,
                'previous_tier' => $this->previousTier->value,
                'new_tier' => $this->newTier->value,
                'tier_upgraded' => $this->tierUpgraded,
                'reference_id' => $this->referenceId,
                'transaction_date' => $this->transactionDate->toIso8601String(),
            ],
        ];
    }
}
