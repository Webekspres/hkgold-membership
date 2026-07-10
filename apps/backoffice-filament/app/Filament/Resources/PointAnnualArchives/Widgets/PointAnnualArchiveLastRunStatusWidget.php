<?php

declare(strict_types=1);

namespace App\Filament\Resources\PointAnnualArchives\Widgets;

use App\Services\Loyalty\PointAnnualArchiveService;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class PointAnnualArchiveLastRunStatusWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = 'full';

    protected int|array|null $columns = 4;

    protected ?string $pollingInterval = '5s';

    /**
     * @return array<Stat>
     */
    protected function getStats(): array
    {
        $status = app(PointAnnualArchiveService::class)->getLastRunStatus();
        $targetYear = (int) $status['target_year'];
        $state = (string) $status['status'];

        $statusLabel = match ($state) {
            'queued' => 'Menunggu antrean',
            'processing' => 'Sedang diproses',
            'success' => 'Selesai',
            'failed' => 'Gagal',
            default => 'Belum dijalankan',
        };

        $completedAt = $status['completed_at'] !== null
            ? Carbon::parse($status['completed_at'])->translatedFormat('d M Y H:i')
            : '—';

        $startedAt = $status['started_at'] !== null
            ? Carbon::parse($status['started_at'])->translatedFormat('d M Y H:i')
            : '—';

        $error = $status['error'] !== null && $status['error'] !== ''
            ? (string) $status['error']
            : 'Tidak ada error';

        return [
            Stat::make('Target Arsip', (string) $targetYear)
                ->description('Tahun yang diproses'),

            Stat::make('Status Run Terakhir', $statusLabel)
                ->description('Diminta: '.((string) ($status['requested_by'] ?? '—')).' | Mulai: '.$startedAt),

            Stat::make('Selesai Pada', $completedAt)
                ->description('Total member: '.number_format((int) ($status['total_members'] ?? 0), 0, ',', '.')),

            Stat::make(
                'Total Poin Dibekukan',
                number_format((int) ($status['frozen_points_total'] ?? 0), 0, ',', '.'),
            )
                ->description($state === 'failed' ? 'Error: '.$error : 'Ringkasan run terakhir'),
        ];
    }
}
