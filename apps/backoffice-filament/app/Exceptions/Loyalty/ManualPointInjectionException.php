<?php

declare(strict_types=1);

namespace App\Exceptions\Loyalty;

use RuntimeException;

class ManualPointInjectionException extends RuntimeException
{
    public function __construct(
        public readonly string $errorCode,
        string $message,
        public readonly array $details = [],
    ) {
        parent::__construct($message);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'success' => false,
            'error' => [
                'code' => $this->errorCode,
                'message' => $this->getMessage(),
                'details' => $this->details,
            ],
        ];
    }

    public static function memberNotFound(): self
    {
        return new self('MEMBER_NOT_FOUND', 'Member tidak ditemukan atau sudah dihapus.');
    }

    public static function memberSuspended(): self
    {
        return new self('MEMBER_SUSPENDED', 'Member sedang ditangguhkan dan tidak dapat menerima suntikan poin.');
    }

    public static function transactionTypeNotFound(): self
    {
        return new self('TRANSACTION_TYPE_NOT_FOUND', 'Jenis transaksi tidak ditemukan.');
    }

    public static function conversionRuleNotFound(): self
    {
        return new self('CONVERSION_RULE_NOT_FOUND', 'Aturan konversi tidak ditemukan untuk kombinasi tier dan jenis transaksi ini.');
    }

    public static function nominalBelowConversionMinimum(string $conversionNominal): self
    {
        return new self(
            'NOMINAL_BELOW_CONVERSION_MINIMUM',
            'Nominal belanja harus minimal Rp '.number_format((float) $conversionNominal, 0, ',', '.').' untuk mendapatkan poin.',
            ['conversion_nominal' => $conversionNominal],
        );
    }

    public static function duplicateReceipt(string $referenceId, int $transactionTypeId): self
    {
        return new self(
            'DUPLICATE_RECEIPT',
            'Nomor struk sudah digunakan untuk jenis transaksi yang sama.',
            [
                'reference_id' => $referenceId,
                'transaction_type_id' => $transactionTypeId,
            ],
        );
    }

    public static function invalidTransactionDate(): self
    {
        return new self('INVALID_TRANSACTION_DATE', 'Tanggal transaksi tidak boleh melebihi hari ini.');
    }

    public static function branchNotFound(): self
    {
        return new self('BRANCH_NOT_FOUND', 'Cabang tidak ditemukan.');
    }

    public static function invalidPurchaseNominal(): self
    {
        return new self('INVALID_PURCHASE_NOMINAL', 'Nominal belanja minimal Rp 1.');
    }
}
