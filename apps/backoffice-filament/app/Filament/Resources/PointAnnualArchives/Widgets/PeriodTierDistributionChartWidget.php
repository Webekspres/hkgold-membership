<?php

declare(strict_types=1);

namespace App\Filament\Resources\PointAnnualArchives\Widgets;

use App\Enums\TierStatus;
use App\Models\PointAnnualArchive;
use App\Models\PointAnnualArchivePeriod;
use Filament\Widgets\ChartWidget;

class PeriodTierDistributionChartWidget extends ChartWidget
{
    protected static ?int $sort = 3;

    protected ?string $heading = 'Sebaran Tier Member per Periode';

    protected ?string $description = 'Jumlah anggota berdasarkan tier di setiap tahun arsip';

    protected ?string $maxHeight = '280px';

    protected int|string|array $columnSpan = 'full';

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'maintainAspectRatio' => false,
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'top',
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'precision' => 0,
                    ],
                ],
            ],
        ];
    }

    protected function getData(): array
    {
        $periods = PointAnnualArchivePeriod::query()
            ->orderBy('archive_year', 'asc')
            ->get();

        $rawCounts = PointAnnualArchive::query()
            ->selectRaw('period_id, last_tier_position, COUNT(*) as total')
            ->groupBy('period_id', 'last_tier_position')
            ->get();

        $countsMap = [];
        foreach ($rawCounts as $row) {
            $countsMap[$row->period_id][$row->last_tier_position->value] = (int) $row->total;
        }

        $datasets = [];
        $colorMap = [
            TierStatus::Silver->value => ['#94a3b8', 'Silver'],
            TierStatus::Gold->value => ['#eab308', 'Gold'],
            TierStatus::Platinum->value => ['#64748b', 'Platinum'],
            TierStatus::Elite->value => ['#3b82f6', 'Elite'],
        ];

        foreach (TierStatus::cases() as $tier) {
            $data = [];
            foreach ($periods as $period) {
                $data[] = $countsMap[$period->id][$tier->value] ?? 0;
            }

            [$color, $label] = $colorMap[$tier->value];

            $datasets[] = [
                'label' => $label,
                'data' => $data,
                'backgroundColor' => $color,
                'borderColor' => $color,
                'borderWidth' => 1,
            ];
        }

        return [
            'datasets' => $datasets,
            'labels' => $periods->pluck('archive_year')->map(fn ($year) => (string) $year)->all(),
        ];
    }
}
