<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\CampaignStatus;
use App\Models\Notification;
use App\Models\NotificationCampaign;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class PruneNotificationsCommand extends Command
{
    protected $signature = 'notifications:prune {--months=3 : Retention period in months} {--dry-run : Display totals to prune without deleting}';

    protected $description = 'Prune old read notifications and completed/failed campaigns based on retention period';

    public function handle(): int
    {
        $months = (int) $this->option('months');

        if ($months < 1) {
            $this->error('Option --months minimal 1.');

            return self::FAILURE;
        }

        $cutoff = Carbon::now()->subMonths($months);
        $isDryRun = (bool) $this->option('dry-run');

        $this->info("Cutoff retensi: {$cutoff->toDateTimeString()}");

        $notificationQuery = Notification::query()
            ->whereNotNull('read_at')
            ->where('created_at', '<', $cutoff);

        $campaignQuery = NotificationCampaign::query()
            ->whereIn('status', [CampaignStatus::Completed, CampaignStatus::Failed])
            ->where('created_at', '<', $cutoff);

        $notificationTotal = (clone $notificationQuery)->count();
        $campaignTotal = (clone $campaignQuery)->count();

        if ($notificationTotal === 0 && $campaignTotal === 0) {
            $this->info('Tidak ada notifikasi atau campaign yang perlu dipangkas.');

            return self::SUCCESS;
        }

        $this->info("Ditemukan {$notificationTotal} notifikasi sudah dibaca dan {$campaignTotal} campaign selesai/gagal.");

        if ($isDryRun) {
            $this->comment('Dry run aktif. Tidak ada data yang dihapus.');

            return self::SUCCESS;
        }

        $notificationsDeleted = $this->pruneInBatches($notificationQuery, 'notifikasi');
        $campaignsDeleted = $this->pruneInBatches($campaignQuery, 'campaign');

        $this->info("Selesai. {$notificationsDeleted} notifikasi dan {$campaignsDeleted} campaign berhasil dihapus.");

        return self::SUCCESS;
    }

    /**
     * @param  Builder<Notification|NotificationCampaign>  $query
     */
    private function pruneInBatches($query, string $label): int
    {
        $total = (clone $query)->count();

        if ($total === 0) {
            return 0;
        }

        $deleted = 0;

        while (true) {
            $ids = (clone $query)
                ->orderBy('id')
                ->limit(500)
                ->pluck('id');

            if ($ids->isEmpty()) {
                break;
            }

            $batchDeleted = $query->getModel()::query()
                ->whereIn('id', $ids->all())
                ->delete();

            $deleted += $batchDeleted;
            $this->line("Menghapus batch {$batchDeleted} {$label}... total {$deleted}/{$total}");
        }

        return $deleted;
    }
}
