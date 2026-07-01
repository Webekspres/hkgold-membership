<?php

declare(strict_types=1);

namespace App\Filament\Resources\PointMutations\Support;

use App\Data\Loyalty\ManualPointInjectionData;
use App\Exceptions\Loyalty\ManualPointInjectionException;
use App\Models\Member;
use App\Services\Loyalty\ManualPointInjectionService;

class ManualPointInjectionFormSupport
{
    public static function memberOptionLabel(?string $memberId): ?string
    {
        if (blank($memberId)) {
            return null;
        }

        $member = Member::query()
            ->with('user')
            ->whereKey($memberId)
            ->first();

        if ($member === null) {
            return null;
        }

        return self::formatMemberLabel($member);
    }

    /**
     * @return array<string, string>
     */
    public static function searchMembers(string $search): array
    {
        return Member::query()
            ->with('user')
            ->whereHas('user')
            ->where(function ($query) use ($search): void {
                $query->where('member_number', 'like', "%{$search}%")
                    ->orWhereHas('user', fn ($userQuery) => $userQuery->where('full_name', 'like', "%{$search}%"));
            })
            ->orderBy('member_number')
            ->limit(50)
            ->get()
            ->mapWithKeys(fn (Member $member): array => [
                $member->id => self::formatMemberLabel($member),
            ])
            ->all();
    }

    public static function formatMemberLabel(Member $member): string
    {
        return sprintf('%s - %s', $member->user?->full_name ?? '—', $member->member_number);
    }

    /**
     * @param  array<string, mixed>  $state
     */
    public static function isFormComplete(array $state): bool
    {
        if (blank($state['member_id'] ?? null)) {
            return false;
        }

        if (blank($state['transaction_type_id'] ?? null)) {
            return false;
        }

        if (blank($state['transaction_date'] ?? null)) {
            return false;
        }

        $nominal = $state['purchase_nominal'] ?? null;

        return filled($nominal) && (float) $nominal >= 1;
    }

    /**
     * @param  array<string, mixed>  $state
     */
    public static function buildPreviewHtml(array $state): string
    {
        if (! self::isFormComplete($state)) {
            return '<p class="text-sm text-gray-500 dark:text-gray-400">Lengkapi data transaksi pada langkah sebelumnya, lalu klik <strong>Lanjut</strong>.</p>';
        }

        try {
            $preview = app(ManualPointInjectionService::class)
                ->preview(ManualPointInjectionData::fromArray($state));
        } catch (ManualPointInjectionException $exception) {
            return '<p class="text-sm text-danger-600 dark:text-danger-400">'.e($exception->getMessage()).'</p>';
        }

        $nominal = number_format((float) $preview['purchase_nominal'], 0, ',', '.');
        $tierLine = $preview['tier_upgraded']
            ? sprintf(
                '<li>Tier: <strong>%s</strong> → <strong>%s</strong> (naik)</li>',
                $preview['previous_tier'],
                $preview['new_tier'],
            )
            : sprintf('<li>Tier: <strong>%s</strong> (tidak berubah)</li>', $preview['previous_tier']);

        $branchLine = filled($preview['branch_name'] ?? null)
            ? sprintf('<li>Cabang: <strong>%s</strong></li>', e($preview['branch_name']))
            : '<li>Cabang: <em>—</em></li>';

        $referenceLine = filled($preview['receipt_number'] ?? null)
            ? sprintf('<li>Nomor struk: <strong>%s</strong></li>', e($preview['receipt_number']))
            : '<li>Nomor struk: <em>—</em></li>';

        return <<<HTML
            <ul class="space-y-1 text-sm">
                <li>Member: <strong>{$preview['member_name']}</strong> ({$preview['member_number']})</li>
                {$branchLine}
                <li>Jenis transaksi: <strong>{$preview['transaction_type']}</strong></li>
                <li>Nominal belanja: <strong>Rp {$nominal}</strong></li>
                <li>Poin ditambahkan: <strong>+{$preview['points_issued']}</strong></li>
                <li>Saldo: <strong>{$preview['previous_balance']}</strong> → <strong>{$preview['new_balance']}</strong></li>
                {$tierLine}
                {$referenceLine}
                <li>Tanggal transaksi: <strong>{$preview['transaction_date']}</strong></li>
            </ul>
        HTML;
    }
}
