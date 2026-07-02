<?php

declare(strict_types=1);

namespace App\Filament\Resources\PointAnnualArchives\Widgets;

use App\Models\PointAnnualArchivePeriod;
use Filament\Widgets\ChartWidget;

class PeriodTotalMembersChartWidget extends ChartWidget
{
    protected static ?int $sort = 1;

    protected ?string $heading = 'Total Member per Periode';

    protected ?string $description = 'Jumlah anggota yang diarsipkan per tahun';

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
                    'display' => false,
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
                    'label' => 'Total Member',
                    'data' => $periods->pluck('total_members')->all(),
                    'fill' => 'start',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'borderColor' => 'rgb(59, 130, 246)',
                    'tension' => 0.3,
                ],
            ],
            'labels' => $periods->pluck('archive_year')->map(fn ($year) => (string) $year)->all(),
        ];
    }
}
