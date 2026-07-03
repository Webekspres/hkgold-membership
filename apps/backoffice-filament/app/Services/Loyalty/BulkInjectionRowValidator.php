<?php

declare(strict_types=1);

namespace App\Services\Loyalty;

use App\Data\Loyalty\BulkInjectionRowValidationResult;
use App\Models\Branch;
use App\Models\Member;
use App\Models\PointInjectionDetail;
use App\Models\PointMutation;
use App\Models\TransactionType;
use App\Support\BulkInjectionDateParser;
use Illuminate\Support\Carbon;

class BulkInjectionRowValidator
{
    /**
     * @param  array<string, mixed>  $row
     * @param  array<string, true>  $seenReceipts
     */
    public function validate(array $row, array &$seenReceipts): BulkInjectionRowValidationResult
    {
        $rawMemberNumber = trim((string) ($row['raw_member_number'] ?? ''));
        $rawBranchCode = trim((string) ($row['raw_branch_code'] ?? ''));
        $purchaseNominal = $this->normalizePurchaseNominal($row['purchase_nominal'] ?? null);
        $receiptNumber = $this->normalizeReceiptNumber($row['receipt_number'] ?? null);

        if ($rawMemberNumber === '') {
            return BulkInjectionRowValidationResult::failed('Member tidak ditemukan', $purchaseNominal, $rawBranchCode);
        }

        $member = Member::query()
            ->where('member_number', $rawMemberNumber)
            ->first();

        if ($member === null) {
            return BulkInjectionRowValidationResult::failed('Member tidak ditemukan', $purchaseNominal, $rawBranchCode);
        }

        if ($member->is_suspended) {
            return BulkInjectionRowValidationResult::failed('Member dinonaktifkan', $purchaseNominal, $rawBranchCode);
        }

        if ($receiptNumber === null) {
            return BulkInjectionRowValidationResult::failed('Nomor struk wajib diisi', $purchaseNominal, $rawBranchCode);
        }

        if (bccomp($purchaseNominal, '0.00', 2) <= 0) {
            return BulkInjectionRowValidationResult::failed('Nominal tidak valid', $purchaseNominal, $rawBranchCode);
        }

        $transactionType = $this->resolveTransactionType($row['transaction_type_key'] ?? null);

        if ($transactionType === null) {
            return BulkInjectionRowValidationResult::failed('Jenis transaksi tidak ditemukan', $purchaseNominal, $rawBranchCode);
        }

        $transactionDate = $this->parseTransactionDate($row['transaction_date'] ?? null);

        if ($transactionDate === null) {
            return BulkInjectionRowValidationResult::failedWithType(
                'Tanggal transaksi tidak valid',
                $transactionType->id,
                $purchaseNominal,
                $receiptNumber,
                $rawBranchCode,
            );
        }

        if ($transactionDate->copy()->startOfDay()->gt(Carbon::today())) {
            return BulkInjectionRowValidationResult::failedWithType(
                'Tanggal transaksi tidak boleh melebihi hari ini',
                $transactionType->id,
                $purchaseNominal,
                $receiptNumber,
                $rawBranchCode,
                $transactionDate,
            );
        }

        if ($rawBranchCode !== '' && ! Branch::query()->where('branch_code', $rawBranchCode)->exists()) {
            return BulkInjectionRowValidationResult::failedWithType(
                'Kode cabang tidak valid',
                $transactionType->id,
                $purchaseNominal,
                $receiptNumber,
                $rawBranchCode,
                $transactionDate,
            );
        }

        if ($this->receiptExistsInMutations($receiptNumber, $transactionType->id)) {
            return BulkInjectionRowValidationResult::failedWithType(
                'Nomor struk sudah dipakai',
                $transactionType->id,
                $purchaseNominal,
                $receiptNumber,
                $rawBranchCode,
                $transactionDate,
            );
        }

        $receiptKey = $this->receiptKey($receiptNumber, $transactionType->id);

        if (isset($seenReceipts[$receiptKey])) {
            return BulkInjectionRowValidationResult::failedWithType(
                'Nomor struk duplikat dalam file',
                $transactionType->id,
                $purchaseNominal,
                $receiptNumber,
                $rawBranchCode,
                $transactionDate,
            );
        }

        $seenReceipts[$receiptKey] = true;

        return BulkInjectionRowValidationResult::success(
            transactionTypeId: $transactionType->id,
            transactionDate: $transactionDate,
            purchaseNominal: $purchaseNominal,
            receiptNumber: $receiptNumber,
            rawBranchCode: $rawBranchCode,
        );
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    public function validateDetail(PointInjectionDetail $detail, array $overrides = []): BulkInjectionRowValidationResult
    {
        $seenReceipts = $this->buildSeenReceiptsForBatch($detail);

        return $this->validate($this->buildRowFromDetail($detail, $overrides), $seenReceipts);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function buildRowFromDetail(PointInjectionDetail $detail, array $overrides): array
    {
        $transactionTypeId = $overrides['transaction_type_id'] ?? $detail->transaction_type_id;
        $transactionType = $transactionTypeId !== null
            ? TransactionType::query()->find($transactionTypeId)
            : null;

        return [
            'raw_member_number' => $overrides['raw_member_number'] ?? $detail->raw_member_number,
            'receipt_number' => $overrides['receipt_number'] ?? $detail->receipt_number,
            'purchase_nominal' => $overrides['purchase_nominal'] ?? $detail->purchase_nominal,
            'transaction_date' => $overrides['transaction_date'] ?? $detail->transaction_date,
            'raw_branch_code' => $overrides['raw_branch_code'] ?? $detail->raw_branch_code,
            'transaction_type_key' => $transactionType?->type_key,
        ];
    }

    /**
     * @return array<string, true>
     */
    private function buildSeenReceiptsForBatch(PointInjectionDetail $detail): array
    {
        $seenReceipts = [];

        $siblings = PointInjectionDetail::query()
            ->where('batch_id', $detail->batch_id)
            ->where('id', '!=', $detail->id)
            ->whereNotNull('receipt_number')
            ->where('receipt_number', '!=', '')
            ->get(['receipt_number', 'transaction_type_id']);

        foreach ($siblings as $sibling) {
            if ($sibling->transaction_type_id === null) {
                continue;
            }

            $receiptNumber = $this->normalizeReceiptNumber($sibling->receipt_number);

            if ($receiptNumber === null) {
                continue;
            }

            $seenReceipts[$this->receiptKey($receiptNumber, $sibling->transaction_type_id)] = true;
        }

        return $seenReceipts;
    }

    private function resolveTransactionType(mixed $value): ?TransactionType
    {
        $normalized = mb_strtoupper(trim((string) $value));

        if ($normalized === '') {
            return null;
        }

        return TransactionType::query()
            ->whereRaw('UPPER(type_key) = ?', [$normalized])
            ->orWhereRaw('UPPER(display_name) = ?', [$normalized])
            ->first();
    }

    private function parseTransactionDate(mixed $value): ?Carbon
    {
        return BulkInjectionDateParser::parse($value);
    }

    private function normalizePurchaseNominal(mixed $value): string
    {
        if (! is_numeric($value)) {
            return '0.00';
        }

        return number_format((float) $value, 2, '.', '');
    }

    private function normalizeReceiptNumber(mixed $value): ?string
    {
        if (blank($value)) {
            return null;
        }

        return mb_strtoupper(trim((string) $value));
    }

    private function receiptExistsInMutations(string $receiptNumber, int $transactionTypeId): bool
    {
        return PointMutation::query()
            ->where('receipt_number', $receiptNumber)
            ->where('transaction_type_id', $transactionTypeId)
            ->exists();
    }

    private function receiptKey(string $receiptNumber, int $transactionTypeId): string
    {
        return $receiptNumber.'|'.$transactionTypeId;
    }
}
