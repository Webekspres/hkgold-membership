<?php

declare(strict_types=1);

namespace App\Filament\Resources\RedeemTokens\Support;

use App\Enums\Role;
use App\Exceptions\Redeem\RedeemConfirmationException;
use App\Filament\Resources\RedeemTokens\Tables\RedeemTokensTable;
use App\Filament\Support\IndonesianDateTimeFormatter;
use App\Models\RedeemToken;
use App\Services\Redeem\FonnteOtpClient;
use Illuminate\Support\Facades\Auth;

class VerifyRedeemTokenFormSupport
{
    public static function normalizeTokenCode(string $raw): ?string
    {
        $trimmed = strtoupper(trim($raw));

        if (preg_match('/^[A-Z0-9]{10}$/', $trimmed)) {
            return $trimmed;
        }

        if (preg_match('/[A-Z0-9]{10}/', $trimmed, $matches)) {
            return $matches[0];
        }

        return null;
    }

    public static function findAvailableToken(string $tokenCode): ?RedeemToken
    {
        $normalized = self::normalizeTokenCode($tokenCode);
        if ($normalized === null) {
            return null;
        }

        return RedeemToken::query()
            ->available()
            ->with(['member.user', 'reward', 'branch'])
            ->where('token_code', $normalized)
            ->first();
    }

    public static function sendRedeemOtp(RedeemToken $token): void
    {
        if ($token->member === null) {
            throw RedeemConfirmationException::tokenNotFound();
        }

        app(FonnteOtpClient::class)->send(
            (string) $token->member->phone_number,
            (string) $token->token_code,
        );
    }

    public static function previewTokenFromState(array $state): ?RedeemToken
    {
        $tokenCode = self::normalizeTokenCode((string) ($state['token_code'] ?? ''));
        if ($tokenCode === null) {
            return null;
        }

        return RedeemToken::query()
            ->available()
            ->with(['member.user', 'reward.rewardImages.media', 'branch'])
            ->where('token_code', $tokenCode)
            ->first();
    }

    /**
     * @return array{
     *     error: string|null,
     *     sections: list<array{
     *         title: string,
     *         rows: list<array{
     *             label: string,
     *             value: string,
     *             badgeColor?: string,
     *             imageUrl?: string|null,
     *             fullWidth?: bool
     *         }>
     *     }>
     * }
     */
    public static function buildPreviewViewData(array $state): array
    {
        $tokenCode = self::normalizeTokenCode((string) ($state['token_code'] ?? '')) ?? '';
        if ($tokenCode === '') {
            return [
                'error' => 'Masukkan kode token pada langkah sebelumnya.',
                'sections' => [],
            ];
        }

        $token = self::previewTokenFromState($state);
        if ($token === null) {
            return [
                'error' => 'Token tidak tersedia atau sudah kedaluwarsa.',
                'sections' => [],
            ];
        }

        return [
            'error' => null,
            'sections' => [
                [
                    'title' => 'Status Kupon',
                    'rows' => [
                        ['label' => 'Kode Token', 'value' => $token->token_code],
                        [
                            'label' => 'Status',
                            'value' => RedeemTokensTable::statusLabel($token),
                            'badgeColor' => RedeemTokensTable::statusColor($token),
                        ],
                        ['label' => 'Poin Ditahan', 'value' => number_format((int) $token->held_points, 0, ',', '.')],
                        [
                            'label' => 'Kedaluwarsa',
                            'value' => IndonesianDateTimeFormatter::tableDate($token->expired_at) ?? '-',
                        ],
                        [
                            'label' => 'Dibuat',
                            'value' => IndonesianDateTimeFormatter::tableDate($token->created_at) ?? '-',
                        ],
                    ],
                ],
                [
                    'title' => 'Member',
                    'rows' => [
                        ['label' => 'Nama', 'value' => (string) ($token->member?->user?->full_name ?? '-')],
                        ['label' => 'Nomor Member', 'value' => (string) ($token->member?->member_number ?? '-')],
                        ['label' => 'No. HP', 'value' => (string) ($token->member?->phone_number ?? '-')],
                    ],
                ],
                [
                    'title' => 'Reward',
                    'rows' => [
                        [
                            'label' => 'Nama Reward',
                            'value' => (string) ($token->reward?->name ?? '-'),
                            'imageUrl' => $token->reward?->rewardImages->first()?->media?->file_url,
                            'fullWidth' => true,
                        ],
                        ['label' => 'SKU', 'value' => (string) ($token->reward?->sku ?? '-')],
                        [
                            'label' => 'Poin Dibutuhkan',
                            'value' => number_format((int) ($token->reward?->points_required ?? 0), 0, ',', '.'),
                        ],
                    ],
                ],
                [
                    'title' => 'Cabang',
                    'rows' => [
                        ['label' => 'Nama Cabang', 'value' => (string) ($token->branch?->name ?? '-')],
                        [
                            'label' => 'Alamat',
                            'value' => (string) ($token->branch?->address ?? '-'),
                            'fullWidth' => true,
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array{
     *     error: string|null,
     *     tokenCode: string,
     *     memberName: string,
     *     memberNumber: string,
     *     maskedPhone: string,
     *     rewardName: string,
     *     rewardImages: list<string>,
     *     pointsLabel: string,
     *     branchName: string,
     *     branchIsCurrent: bool,
     *     staffName: string,
     *     staffId: string|null,
     *     otpStatus: string,
     *     otpStatusColor: string
     * }
     */
    public static function buildOtpStepViewData(array $state): array
    {
        $tokenCode = self::normalizeTokenCode((string) ($state['token_code'] ?? '')) ?? '';
        if ($tokenCode === '') {
            return self::otpStepError('Masukkan kode token pada langkah sebelumnya.');
        }

        $token = self::previewTokenFromState($state);
        if ($token === null) {
            return self::otpStepError('Token tidak tersedia atau sudah kedaluwarsa.');
        }

        $user = Auth::user();
        $staffId = $user?->staff?->id;
        $branchIsCurrent = $user !== null
            && $user->role === Role::StoreManager
            && $user->staff?->branch_id === $token->branch_id;

        $rewardImages = $token->reward?->rewardImages
            ->filter(fn ($rewardImage): bool => filled($rewardImage->media?->file_url))
            ->take(2)
            ->map(fn ($rewardImage): string => (string) $rewardImage->media?->file_url)
            ->values()
            ->all() ?? [];

        return [
            'error' => null,
            'tokenCode' => $token->token_code,
            'memberName' => (string) ($token->member?->user?->full_name ?? '-'),
            'memberNumber' => (string) ($token->member?->member_number ?? '-'),
            'maskedPhone' => self::maskPhoneNumber((string) ($token->member?->phone_number ?? '')),
            'rewardName' => (string) ($token->reward?->name ?? '-'),
            'rewardImages' => $rewardImages,
            'pointsLabel' => number_format((int) $token->held_points, 0, ',', '.').' pts',
            'branchName' => (string) ($token->branch?->name ?? '-'),
            'branchIsCurrent' => $branchIsCurrent,
            'staffName' => (string) ($user?->full_name ?? '-'),
            'staffId' => $staffId !== null ? (string) $staffId : null,
            'otpStatus' => 'SUCCESS',
            'otpStatusColor' => 'success',
        ];
    }

    public static function maskPhoneNumber(string $phone): string
    {
        $digits = preg_replace('/\D/', '', $phone) ?? '';

        if (strlen($digits) < 8) {
            return '****';
        }

        return substr($digits, 0, 4).'****'.substr($digits, -3);
    }

    /**
     * @return array{
     *     error: string,
     *     tokenCode: string,
     *     memberName: string,
     *     memberNumber: string,
     *     maskedPhone: string,
     *     rewardName: string,
     *     rewardImages: list<string>,
     *     pointsLabel: string,
     *     branchName: string,
     *     branchIsCurrent: bool,
     *     staffName: string,
     *     staffId: string|null,
     *     otpStatus: string,
     *     otpStatusColor: string
     * }
     */
    private static function otpStepError(string $message): array
    {
        return [
            'error' => $message,
            'tokenCode' => '',
            'memberName' => '-',
            'memberNumber' => '-',
            'maskedPhone' => '****',
            'rewardName' => '-',
            'rewardImages' => [],
            'pointsLabel' => '-',
            'branchName' => '-',
            'branchIsCurrent' => false,
            'staffName' => '-',
            'staffId' => null,
            'otpStatus' => 'FAILED',
            'otpStatusColor' => 'danger',
        ];
    }

    /**
     * @param  array<string, mixed>  $state
     */
    public static function buildPreviewHtml(array $state): string
    {
        $viewData = self::buildPreviewViewData($state);

        if ($viewData['error'] !== null) {
            $class = str_contains($viewData['error'], 'kedaluwarsa')
                ? 'text-danger-600 dark:text-danger-400'
                : 'text-gray-500 dark:text-gray-400';

            return '<p class="text-sm '.$class.'">'.e($viewData['error']).'</p>';
        }

        $rows = collect($viewData['sections'])
            ->flatMap(fn (array $section): array => $section['rows'])
            ->pluck('value')
            ->implode(' ');

        return $rows;
    }
}
