<?php

declare(strict_types=1);

namespace App\Filament\Resources\PointAnnualArchives\Widgets;

use App\Enums\TierStatus;
use App\Models\PointAnnualArchive;
use App\Models\PointAnnualArchivePeriod;
use Filament\Widgets\ChartWidget;

class PeriodDetailTierChartWidget extends ChartWidget
{
    public ?PointAnnualArchivePeriod $record = null;

    protected ?string $heading = 'Sebaran Tier Member';

    protected ?string $maxHeight = '220px';

    protected function getType(): string
    {
        return 'pie';
    }

    protected function getOptions(): array
    {
        return [
            'maintainAspectRatio' => false,
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'right',
                ],
            ],
        ];
    }

    protected function getData(): array
    {
        if ($this->record === null) {
            return [];
        }

        $counts = PointAnnualArchive::query()
            ->where('period_id', $this->record->id)
            ->selectRaw('last_tier_position, COUNT(*) as total')
            ->groupBy('last_tier_position')
            ->pluck('total', 'last_tier_position');

        $total = (int) $counts->sum();

        $labels = [];
        $data = [];
        $backgroundColors = [];
        $borderColors = [];

        $colorMap = [
            TierStatus::Silver->value => ['#94a3b8', '#cbd5e1'],
            TierStatus::Gold->value => ['#eab308', '#fde047'],
            TierStatus::Platinum->value => ['#64748b', '#94a3b8'],
            TierStatus::Sapphire->value => ['#3b82f6', '#93c5fd'],
        ];

        foreach (TierStatus::cases() as $tier) {
            $count = (int) ($counts[$tier->value] ?? 0);
            $percentage = $total > 0 ? round(($count / $total) * 100, 1) : 0;

            $labels[] = sprintf('%s (%s%%)', $tier->value, $percentage);
            $data[] = $count;
            [$backgroundColors[], $borderColors[]] = $colorMap[$tier->value];
        }

        return [
            'datasets' => [
                [
                    'data' => $data,
                    'backgroundColor' => $backgroundColors,
                    'borderColor' => $borderColors,
                    'borderWidth' => 1,
                ],
            ],
            'labels' => $labels,
        ];
    }
}
