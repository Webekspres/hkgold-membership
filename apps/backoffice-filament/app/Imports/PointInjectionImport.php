<?php

declare(strict_types=1);

namespace App\Imports;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class PointInjectionImport implements SkipsEmptyRows, ToCollection, WithHeadingRow
{
    /**
     * @var list<array{
     *     row_number: int,
     *     transaction_date: mixed,
     *     raw_member_number: mixed,
     *     receipt_number: mixed,
     *     purchase_nominal: mixed,
     *     transaction_type_key: mixed,
     *     raw_branch_code: string|null,
     * }>
     */
    private array $rows = [];

    /**
     * @var array<string, list<string>>
     */
    private const FIELD_ALIASES = [
        'transaction_date' => ['tgl_transaksi', 'transaction_date'],
        'raw_member_number' => ['nomor_member', 'member_number'],
        'receipt_number' => ['nomor_struk', 'receipt_number'],
        'purchase_nominal' => ['nominal_transaksi', 'purchase_nominal'],
        'transaction_type_key' => ['jenis_transaksi', 'transaction_type'],
        'raw_branch_code' => ['branch_code'],
    ];

    public function collection(Collection $collection): void
    {
        foreach ($collection as $index => $row) {
            $mapped = $this->mapRow($row);

            if ($this->isEmptyRow($mapped)) {
                continue;
            }

            $mapped['row_number'] = (int) $index + 2;

            $this->rows[] = $mapped;
        }
    }

    /**
     * @return list<array{
     *     row_number: int,
     *     transaction_date: mixed,
     *     raw_member_number: mixed,
     *     receipt_number: mixed,
     *     purchase_nominal: mixed,
     *     transaction_type_key: mixed,
     *     raw_branch_code: string|null,
     * }>
     */
    public function getRows(): array
    {
        return $this->rows;
    }

    /**
     * @return array{
     *     transaction_date: mixed,
     *     raw_member_number: mixed,
     *     receipt_number: mixed,
     *     purchase_nominal: mixed,
     *     transaction_type_key: mixed,
     *     raw_branch_code: string|null,
     * }
     */
    private function mapRow(Collection $row): array
    {
        $normalized = $this->normalizeRowKeys($row);

        return [
            'transaction_date' => $this->resolveField($normalized, 'transaction_date'),
            'raw_member_number' => $this->resolveField($normalized, 'raw_member_number'),
            'receipt_number' => $this->resolveField($normalized, 'receipt_number'),
            'purchase_nominal' => $this->resolveField($normalized, 'purchase_nominal'),
            'transaction_type_key' => $this->resolveField($normalized, 'transaction_type_key'),
            'raw_branch_code' => $this->nullableString($this->resolveField($normalized, 'raw_branch_code')),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function normalizeRowKeys(Collection $row): array
    {
        $normalized = [];

        foreach ($row->toArray() as $key => $value) {
            $normalized[$this->normalizeHeader((string) $key)] = $value;
        }

        return $normalized;
    }

    private function normalizeHeader(string $header): string
    {
        return Str::slug(trim($header), '_');
    }

    /**
     * @param  array<string, mixed>  $row
     */
    private function resolveField(array $row, string $field): mixed
    {
        foreach (self::FIELD_ALIASES[$field] as $alias) {
            if (array_key_exists($alias, $row)) {
                return $row[$alias];
            }
        }

        return null;
    }

    private function nullableString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $stringValue = trim((string) $value);

        return $stringValue === '' ? null : $stringValue;
    }

    /**
     * @param  array{
     *     transaction_date: mixed,
     *     raw_member_number: mixed,
     *     receipt_number: mixed,
     *     purchase_nominal: mixed,
     *     transaction_type_key: mixed,
     *     raw_branch_code: string|null,
     * }  $row
     */
    private function isEmptyRow(array $row): bool
    {
        foreach ($row as $value) {
            if ($value !== null && trim((string) $value) !== '') {
                return false;
            }
        }

        return true;
    }
}
