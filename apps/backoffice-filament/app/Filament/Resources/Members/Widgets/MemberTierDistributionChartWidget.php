<?php

declare(strict_types=1);

namespace App\Filament\Resources\Members\Widgets;

use App\Enums\TierStatus;
use App\Models\Member;
use Filament\Widgets\ChartWidget;

class MemberTierDistributionChartWidget extends ChartWidget
{
    protected static ?int $sort = 2;

    protected ?string $heading = 'Sebaran Tier Member';

    protected ?string $description = 'Persentase member per tier';

    protected ?string $maxHeight = '260px';

    protected int|string|array $columnSpan = [
        'default' => 'full',
        'md' => 1,
        'xl' => 1,
    ];

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getOptions(): array
    {
        return [
            'maintainAspectRatio' => false,
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'bottom',
                ],
                'tooltip' => [
                    'enabled' => true,
                    'callbacks' => [],
                ],
            ],
        ];
    }

    protected function getData(): array
    {
        $counts = Member::query()
            ->selectRaw('current_tier, COUNT(*) as total')
            ->groupBy('current_tier')
            ->pluck('total', 'current_tier');

        $total = (int) $counts->sum();

        $labels = [];
        $data = [];
        $backgroundColors = [];
        $borderColors = [];

        $colorMap = [
            TierStatus::Silver->value => ['#94a3b8', '#cbd5e1'],
            TierStatus::Gold->value => ['#eab308', '#fde047'],
            TierStatus::Platinum->value => ['#64748b', '#94a3b8'],
            TierStatus::Elite->value => ['#3b82f6', '#93c5fd'],
        ];

        foreach (TierStatus::cases() as $tier) {
            $count = (int) ($counts[$tier->value] ?? 0);
            $percentage = $total > 0 ? round(($count / $total) * 100, 1) : 0;

            $labels[] = sprintf('%s (%s%%)', $this->tierLabel($tier), $percentage);
            $data[] = $count;
            [$backgroundColors[], $borderColors[]] = $colorMap[$tier->value];
        }

        return [
            'datasets' => [
                [
                    'label' => 'Member',
                    'data' => $data,
                    'backgroundColor' => $backgroundColors,
                    'borderColor' => $borderColors,
                    'borderWidth' => 1,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function tierLabel(TierStatus $tier): string
    {
        return match ($tier) {
            TierStatus::Silver => 'Silver',
            TierStatus::Gold => 'Gold',
            TierStatus::Platinum => 'Platinum',
            TierStatus::Elite => 'Elite',
        };
    }
}
