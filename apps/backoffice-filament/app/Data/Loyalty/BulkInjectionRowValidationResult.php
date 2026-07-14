<?php

declare(strict_types=1);

namespace App\Data\Loyalty;

use Carbon\CarbonInterface;

readonly class BulkInjectionRowValidationResult
{
    public function __construct(
        private bool $valid,
        private ?string $errorMessage,
        private ?int $transactionTypeId,
        private ?CarbonInterface $transactionDate,
        private string $purchaseNominal,
        private ?string $receiptNumber,
        private string $rawBranchCode,
    ) {}

    public static function failed(string $errorMessage, string $purchaseNominal = '0.00', string $rawBranchCode = ''): self
    {
        return new self(
            valid: false,
            errorMessage: $errorMessage,
            transactionTypeId: null,
            transactionDate: null,
            purchaseNominal: $purchaseNominal,
            receiptNumber: null,
            rawBranchCode: $rawBranchCode,
        );
    }

    public static function failedWithType(
        string $errorMessage,
        int $transactionTypeId,
        string $purchaseNominal,
        ?string $receiptNumber,
        string $rawBranchCode,
        ?CarbonInterface $transactionDate = null,
    ): self {
        return new self(
            valid: false,
            errorMessage: $errorMessage,
            transactionTypeId: $transactionTypeId,
            transactionDate: $transactionDate,
            purchaseNominal: $purchaseNominal,
            receiptNumber: $receiptNumber,
            rawBranchCode: $rawBranchCode,
        );
    }

    public static function success(
        int $transactionTypeId,
        CarbonInterface $transactionDate,
        string $purchaseNominal,
        string $receiptNumber,
        string $rawBranchCode,
    ): self {
        return new self(
            valid: true,
            errorMessage: null,
            transactionTypeId: $transactionTypeId,
            transactionDate: $transactionDate,
            purchaseNominal: $purchaseNominal,
            receiptNumber: $receiptNumber,
            rawBranchCode: $rawBranchCode,
        );
    }

    public function isValid(): bool
    {
        return $this->valid;
    }

    public function errorMessage(): ?string
    {
        return $this->errorMessage;
    }

    public function transactionTypeId(): ?int
    {
        return $this->transactionTypeId;
    }

    public function transactionDate(): ?CarbonInterface
    {
        return $this->transactionDate;
    }

    public function purchaseNominal(): string
    {
        return $this->purchaseNominal;
    }

    public function receiptNumber(): ?string
    {
        return $this->receiptNumber;
    }

    public function rawBranchCode(): string
    {
        return $this->rawBranchCode;
    }
}
