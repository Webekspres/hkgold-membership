<?php

declare(strict_types=1);

namespace App\Filament\Resources\NotificationCampaigns\Support;

use App\Enums\CampaignStatus;
use App\Enums\NotificationPlatform;
use App\Enums\TierStatus;
use App\Models\PointInjectionBatch;
use Illuminate\Support\Str;

class BroadcastNotificationFormSupport
{
    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public static function buildCriteria(array $data): array
    {
        $type = (string) ($data['audience_type'] ?? '');

        return match ($type) {
            'tier' => [
                'type' => 'tier',
                'tier' => (string) ($data['tier'] ?? ''),
            ],
            'batch' => [
                'type' => 'batch',
                'batch_id' => (string) ($data['batch_id'] ?? ''),
            ],
            default => [
                'type' => 'all_active_members',
            ],
        };
    }

    /**
     * @return array<string, mixed>
     */
    public static function normalizeJsonObjectAttribute(mixed $value): array
    {
        if ($value === null) {
            return [];
        }

        if (is_array($value)) {
            return $value;
        }

        if (! is_string($value)) {
            return [];
        }

        $decoded = json_decode($value, true);

        return is_array($decoded) ? $decoded : [];
    }

    /**
     * @return array<int, string>
     */
    public static function normalizeJsonAttribute(mixed $value): array
    {
        if ($value === null) {
            return [];
        }

        if (is_array($value)) {
            return array_values(array_map(
                static fn (mixed $item): string => (string) $item,
                $value,
            ));
        }

        if (! is_string($value)) {
            return [];
        }

        $decoded = json_decode($value, true);

        if (is_array($decoded)) {
            return array_values(array_map(
                static fn (mixed $item): string => (string) $item,
                $decoded,
            ));
        }

        if (is_string($decoded) && $decoded !== '') {
            return [$decoded];
        }

        return $value !== '' ? [$value] : [];
    }

    /**
     * @param  array<string, mixed>  $criteria
     */
    public static function audienceLabel(array $criteria): string
    {
        $type = (string) ($criteria['type'] ?? '');

        return match ($type) {
            'tier' => sprintf(
                'Member tier %s',
                self::tierLabel((string) ($criteria['tier'] ?? '')),
            ),
            'batch' => sprintf(
                'Batch injeksi %s',
                Str::limit((string) ($criteria['batch_id'] ?? '—'), 8, ''),
            ),
            default => 'Semua member aktif',
        };
    }

    /**
     * @return array<string, string>
     */
    public static function audienceTypeOptions(): array
    {
        return [
            'all_active_members' => 'Semua member aktif',
            'tier' => 'Per tier member',
            'batch' => 'Per batch injeksi poin',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function tierOptions(): array
    {
        return [
            TierStatus::Silver->value => 'Silver',
            TierStatus::Gold->value => 'Gold',
            TierStatus::Platinum->value => 'Platinum',
            TierStatus::Elite->value => 'Elite',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function batchOptions(): array
    {
        return PointInjectionBatch::query()
            ->orderByDesc('uploaded_at')
            ->limit(100)
            ->get()
            ->mapWithKeys(function (PointInjectionBatch $batch): array {
                $label = sprintf(
                    '%s — %s baris',
                    Str::limit($batch->id, 8, ''),
                    number_format($batch->total_rows ?? 0, 0, ',', '.'),
                );

                if ($batch->uploaded_at !== null) {
                    $label .= ' ('.$batch->uploaded_at->format('d M Y').')';
                }

                return [$batch->id => $label];
            })
            ->all();
    }

    public static function tierLabel(string $tier): string
    {
        $status = TierStatus::tryFrom($tier);

        return match ($status) {
            TierStatus::Silver => 'Silver',
            TierStatus::Gold => 'Gold',
            TierStatus::Platinum => 'Platinum',
            TierStatus::Elite => 'Elite',
            default => $tier !== '' ? $tier : '—',
        };
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function buildConfirmationHtml(array $data, int $targetedCount): string
    {
        $criteria = self::buildCriteria($data);
        $title = e((string) ($data['title'] ?? ''));
        $body = nl2br(e((string) ($data['body'] ?? '')));
        $audience = e(self::audienceLabel($criteria));
        $targeted = number_format($targetedCount, 0, ',', '.');

        return <<<HTML
        <div class="space-y-3 text-sm">
            <div><span class="font-medium text-gray-950 dark:text-white">Judul:</span> {$title}</div>
            <div><span class="font-medium text-gray-950 dark:text-white">Isi:</span><br>{$body}</div>
            <div><span class="font-medium text-gray-950 dark:text-white">Platform:</span> Push Aplikasi Mobile</div>
            <div><span class="font-medium text-gray-950 dark:text-white">Audience:</span> {$audience}</div>
            <div><span class="font-medium text-gray-950 dark:text-white">Perkiraan ditarget:</span> <strong>{$targeted}</strong> orang</div>
        </div>
        HTML;
    }

    /**
     * @param  array<int, string>  $platforms
     */
    public static function formatPlatformLabels(array $platforms): string
    {
        $labels = array_map(function (string $platform): string {
            $enum = NotificationPlatform::tryFrom($platform);

            return match ($enum) {
                NotificationPlatform::MobileAppPush => 'Push Aplikasi Mobile',
                NotificationPlatform::WebBrowserPush => 'Push Browser Web',
                NotificationPlatform::WebAdminInApp => 'In-App Admin',
                default => $platform,
            };
        }, $platforms);

        return implode(', ', $labels);
    }

    public static function statusLabel(CampaignStatus $status): string
    {
        return match ($status) {
            CampaignStatus::Pending => 'Menunggu',
            CampaignStatus::Processing => 'Diproses',
            CampaignStatus::Completed => 'Selesai',
            CampaignStatus::Failed => 'Gagal',
        };
    }

    public static function statusColor(CampaignStatus $status): string
    {
        return match ($status) {
            CampaignStatus::Pending => 'warning',
            CampaignStatus::Processing => 'info',
            CampaignStatus::Completed => 'success',
            CampaignStatus::Failed => 'danger',
        };
    }
}
