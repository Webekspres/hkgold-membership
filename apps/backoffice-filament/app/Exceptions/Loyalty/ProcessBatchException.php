<?php

declare(strict_types=1);

namespace App\Exceptions\Loyalty;

use RuntimeException;

class ProcessBatchException extends RuntimeException
{
    public function __construct(
        public readonly string $errorCode,
        string $message,
        public readonly array $details = [],
    ) {
        parent::__construct($message);
    }

    public static function batchAlreadyResolved(): self
    {
        return new self('BATCH_ALREADY_RESOLVED', 'Batch ini sudah pernah diproses.');
    }

    public static function batchAlreadyProcessing(): self
    {
        return new self('BATCH_ALREADY_PROCESSING', 'Batch sedang diproses di background.');
    }

    public static function batchNotReady(string $reason): self
    {
        return new self('BATCH_NOT_READY', $reason);
    }

    public static function importIncomplete(): self
    {
        return new self('IMPORT_INCOMPLETE', 'Import file belum selesai. Tunggu atau gunakan tombol ulangi parsing.');
    }

    public static function detailNotValidated(int $rowNumber): self
    {
        return new self(
            'DETAIL_NOT_VALIDATED',
            'Baris '.$rowNumber.' tidak dalam status tervalidasi.',
            ['row_number' => $rowNumber],
        );
    }

    public static function memberNotFound(string $memberNumber): self
    {
        return new self(
            'MEMBER_NOT_FOUND',
            'Member '.$memberNumber.' tidak ditemukan atau sudah dihapus.',
            ['member_number' => $memberNumber],
        );
    }

    public static function memberSuspended(string $memberNumber): self
    {
        return new self(
            'MEMBER_SUSPENDED',
            'Member '.$memberNumber.' sedang ditangguhkan.',
            ['member_number' => $memberNumber],
        );
    }

    public static function duplicateReceipt(string $receiptNumber): self
    {
        return new self(
            'DUPLICATE_RECEIPT',
            'Nomor struk '.$receiptNumber.' sudah digunakan untuk jenis transaksi yang sama.',
            ['receipt_number' => $receiptNumber],
        );
    }

    public static function branchNotFound(string $branchCode): self
    {
        return new self(
            'BRANCH_NOT_FOUND',
            'Kode cabang '.$branchCode.' tidak ditemukan.',
            ['branch_code' => $branchCode],
        );
    }
}
