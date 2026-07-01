<?php

declare(strict_types=1);

namespace App\Data\Loyalty;

use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;

readonly class ManualPointInjectionData
{
    public function __construct(
        public string $memberId,
        public ?int $branchId,
        public int $transactionTypeId,
        public string $purchaseNominal,
        public ?string $referenceId,
        public CarbonInterface $transactionDate,
    ) {}

    /**
     * @param  array<string, mixed>  $payload
     */
    public static function fromArray(array $payload): self
    {
        $referenceId = $payload['reference_id'] ?? null;

        return new self(
            memberId: trim((string) $payload['member_id']),
            branchId: filled($payload['branch_id'] ?? null) ? (int) $payload['branch_id'] : null,
            transactionTypeId: (int) $payload['transaction_type_id'],
            purchaseNominal: number_format((float) $payload['purchase_nominal'], 2, '.', ''),
            referenceId: filled($referenceId) ? mb_strtoupper(trim((string) $referenceId)) : null,
            transactionDate: Carbon::parse($payload['transaction_date'])->startOfDay(),
        );
    }
}
