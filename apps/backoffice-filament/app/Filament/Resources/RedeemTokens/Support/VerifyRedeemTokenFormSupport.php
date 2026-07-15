<?php

declare(strict_types=1);

namespace App\Filament\Resources\RedeemTokens\Support;

use App\Models\RedeemToken;

class VerifyRedeemTokenFormSupport
{
    public static function findAvailableToken(string $tokenCode): ?RedeemToken
    {
        return RedeemToken::query()
            ->available()
            ->with(['member.user', 'reward', 'branch'])
            ->where('token_code', strtoupper(trim($tokenCode)))
            ->first();
    }

    /**
     * @param  array<string, mixed>  $state
     */
    public static function buildPreviewHtml(array $state): string
    {
        $tokenCode = (string) ($state['token_code'] ?? '');
        if ($tokenCode === '') {
            return '<p class="text-sm text-gray-500 dark:text-gray-400">Masukkan kode token pada langkah sebelumnya.</p>';
        }

        $token = self::findAvailableToken($tokenCode);
        if ($token === null) {
            return '<p class="text-sm text-danger-600 dark:text-danger-400">Token tidak tersedia atau sudah kedaluwarsa.</p>';
        }

        $memberName = e((string) ($token->member?->user?->full_name ?? '-'));
        $memberNumber = e((string) ($token->member?->member_number ?? '-'));
        $phone = e((string) ($token->member?->phone_number ?? '-'));
        $rewardName = e((string) ($token->reward?->name ?? '-'));
        $branchName = e((string) ($token->branch?->name ?? '-'));
        $heldPoints = number_format((int) $token->held_points, 0, ',', '.');
        $expiredAt = e($token->expired_at?->format('d/m/Y H:i') ?? '-');

        return <<<HTML
            <ul class="space-y-1 text-sm">
                <li>Kode token: <strong>{$token->token_code}</strong></li>
                <li>Member: <strong>{$memberName}</strong> ({$memberNumber})</li>
                <li>No. HP: <strong>{$phone}</strong></li>
                <li>Reward: <strong>{$rewardName}</strong></li>
                <li>Cabang: <strong>{$branchName}</strong></li>
                <li>Poin ditahan: <strong>{$heldPoints}</strong></li>
                <li>Kedaluwarsa: <strong>{$expiredAt}</strong></li>
            </ul>
            <p class="mt-3 text-sm text-gray-600 dark:text-gray-300">OTP akan dikirim ke WhatsApp nomor member saat Anda lanjut ke langkah berikutnya.</p>
        HTML;
    }
}
