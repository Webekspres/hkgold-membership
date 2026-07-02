<?php

declare(strict_types=1);

namespace App\Filament\Resources\PointAnnualArchives\Widgets;

use App\Models\PointAnnualArchivePeriod;
use Filament\Widgets\ChartWidget;

class PeriodFrozenPointsChartWidget extends ChartWidget
{
    protected static ?int $sort = 2;

    protected ?string $heading = 'Tren Poin per Periode';

    protected ?string $description = 'Akumulasi poin dibekukan & ditukarkan per tahun';

    protected ?string $maxHeight = '240px';

    protected int|string|array $columnSpan = [
        'default' => 'full',
        'md' => 1,
        'xl' => 1,
    ];

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'maintainAspectRatio' => false,
            'plugins' => [
                'legend' => [
                    'display' => true,
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

        return [
            'datasets' => [
                [
                    'label' => 'Poin Dibekukan',
                    'data' => $periods->pluck('frozen_points_total')->all(),
                    'fill' => 'start',
                    'backgroundColor' => 'rgba(239, 68, 68, 0.05)',
                    'borderColor' => 'rgb(239, 68, 68)',
                    'tension' => 0.3,
                ],
                [
                    'label' => 'Poin Ditukarkan',
                    'data' => $periods->pluck('redeemed_points_total')->all(),
                    'fill' => 'start',
                    'backgroundColor' => 'rgba(34, 197, 94, 0.05)',
                    'borderColor' => 'rgb(34, 197, 94)',
                    'tension' => 0.3,
                ],
            ],
            'labels' => $periods->pluck('archive_year')->map(fn ($year) => (string) $year)->all(),
        ];
    }
}
