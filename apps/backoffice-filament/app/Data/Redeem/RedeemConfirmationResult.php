<?php

declare(strict_types=1);

namespace App\Data\Redeem;

readonly class RedeemConfirmationResult
{
    public function __construct(
        public string $invoiceId,
        public string $invoiceNumber,
        public string $memberId,
        public string $memberName,
        public string $memberNumber,
        public string $rewardName,
        public string $branchName,
        public int $pointsRedeemed,
        public int $newBalance,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'success' => true,
            'data' => [
                'invoice_id' => $this->invoiceId,
                'invoice_number' => $this->invoiceNumber,
                'member_id' => $this->memberId,
                'member_name' => $this->memberName,
                'member_number' => $this->memberNumber,
                'reward_name' => $this->rewardName,
                'branch_name' => $this->branchName,
                'points_redeemed' => $this->pointsRedeemed,
                'new_balance' => $this->newBalance,
            ],
        ];
    }
}
