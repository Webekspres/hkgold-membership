<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\ActivityLog;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class PruneActivityLogsCommand extends Command
{
    protected $signature = 'activity-log:prune {--months=12 : Retention period in months} {--dry-run : Display total logs to prune without deleting}';

    protected $description = 'Prune old activity logs based on retention period';

    public function handle(): int
    {
        $months = (int) $this->option('months');

        if ($months < 1) {
            $this->error('Option --months minimal 1.');

            return self::FAILURE;
        }

        $cutoff = Carbon::now()->subMonths($months);
        $query = ActivityLog::query()->where('created_at', '<', $cutoff);
        $total = (clone $query)->count();

        if ($total === 0) {
            $this->info('Tidak ada activity log yang perlu dipangkas.');

            return self::SUCCESS;
        }

        $this->info("Ditemukan {$total} activity log sebelum {$cutoff->toDateTimeString()}.");

        if ((bool) $this->option('dry-run')) {
            $this->comment('Dry run aktif. Tidak ada data yang dihapus.');

            return self::SUCCESS;
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

            $batchDeleted = ActivityLog::query()
                ->whereIn('id', $ids->all())
                ->delete();

            $deleted += $batchDeleted;
            $this->line("Menghapus batch {$batchDeleted} log... total {$deleted}/{$total}");
        }

        $this->info("Selesai. {$deleted} activity log berhasil dihapus.");

        return self::SUCCESS;
    }
}
