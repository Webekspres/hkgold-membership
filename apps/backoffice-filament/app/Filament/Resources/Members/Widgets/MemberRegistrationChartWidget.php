<?php

declare(strict_types=1);

namespace App\Filament\Resources\Members\Widgets;

use App\Models\Member;
use Carbon\CarbonPeriod;
use Filament\Widgets\ChartWidget;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\DB;

class MemberRegistrationChartWidget extends ChartWidget
{
    protected static ?int $sort = 1;

    protected ?string $heading = 'Statistik Pendaftaran Member';

    protected string $color = 'primary';

    protected ?string $maxHeight = '260px';

    public ?string $filter = '30';

    protected int|string|array $columnSpan = [
        'default' => 'full',
        'md' => 2,
        'xl' => 2,
    ];

    protected function getFilters(): ?array
    {
        return [
            '7' => '7 hari terakhir',
            '30' => '30 hari terakhir',
            '90' => '90 hari terakhir',
            '365' => '12 bulan terakhir',
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    public function getDescription(): string|Htmlable|null
    {
        return match ((int) $this->filter) {
            7 => 'Jumlah member baru per hari',
            30, 90 => 'Jumlah member baru per minggu',
            365 => 'Jumlah member baru per bulan',
            default => 'Jumlah member baru',
        };
    }

    protected function getOptions(): array
    {
        return [
            'maintainAspectRatio' => false,
            'interaction' => [
                'mode' => 'index',
                'intersect' => false,
            ],
            'plugins' => [
                'legend' => [
                    'display' => true,
                ],
                'tooltip' => [
                    'enabled' => true,
                    'mode' => 'index',
                    'intersect' => false,
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
        $days = (int) $this->filter;

        return match ($days) {
            365 => $this->getMonthlyRegistrationData(),
            30, 90 => $this->getWeeklyRegistrationData($days),
            default => $this->getDailyRegistrationData($days),
        };
    }

    /**
     * @return array{labels: list<string>, datasets: list<array<string, mixed>>}
     */
    protected function getDailyRegistrationData(int $days): array
    {
        $start = now()->subDays($days - 1)->startOfDay();
        $end = now()->endOfDay();

        $registrations = Member::query()
            ->whereBetween('created_at', [$start, $end])
            ->selectRaw('DATE(created_at) as date, COUNT(*) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('total', 'date');

        $labels = [];
        $data = [];

        foreach (CarbonPeriod::create($start, '1 day', $end) as $date) {
            $key = $date->format('Y-m-d');
            $labels[] = $date->translatedFormat('d M');
            $data[] = (int) ($registrations[$key] ?? 0);
        }

        return [
            'datasets' => [
                [
                    'label' => 'Pendaftar',
                    'data' => $data,
                    'fill' => true,
                    'tension' => 0.35,
                ],
            ],
            'labels' => $labels,
        ];
    }

    /**
     * @return array{labels: list<string>, datasets: list<array<string, mixed>>}
     */
    protected function getWeeklyRegistrationData(int $days): array
    {
        $start = now()->subDays($days - 1)->startOfDay();
        $end = now()->endOfDay();

        $registrations = Member::query()
            ->whereBetween('created_at', [$start, $end])
            ->selectRaw('DATE(created_at) as date, COUNT(*) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('total', 'date');

        $labels = [];
        $data = [];
        $cursor = $start->copy();

        while ($cursor->lte($end)) {
            $weekStart = $cursor->copy();
            $weekEnd = $cursor->copy()->endOfWeek()->endOfDay();

            if ($weekEnd->gt($end)) {
                $weekEnd = $end->copy();
            }

            $total = 0;

            foreach (CarbonPeriod::create($weekStart->startOfDay(), '1 day', $weekEnd) as $day) {
                $total += (int) ($registrations[$day->format('Y-m-d')] ?? 0);
            }

            $labels[] = sprintf(
                '%s - %s',
                $weekStart->translatedFormat('d M'),
                $weekEnd->translatedFormat('d M'),
            );
            $data[] = $total;

            $cursor = $weekEnd->copy()->addDay()->startOfDay();
        }

        return [
            'datasets' => [
                [
                    'label' => 'Pendaftar',
                    'data' => $data,
                    'fill' => true,
                    'tension' => 0.35,
                ],
            ],
            'labels' => $labels,
        ];
    }

    /**
     * @return array{labels: list<string>, datasets: list<array<string, mixed>>}
     */
    protected function getMonthlyRegistrationData(): array
    {
        $start = now()->subMonths(11)->startOfMonth();
        $end = now()->endOfMonth();

        $driver = DB::connection()->getDriverName();
        $monthExpression = match ($driver) {
            'pgsql' => "TO_CHAR(created_at, 'YYYY-MM')",
            'sqlite' => "strftime('%Y-%m', created_at)",
            default => "DATE_FORMAT(created_at, '%Y-%m')",
        };

        $registrations = Member::query()
            ->whereBetween('created_at', [$start, $end])
            ->selectRaw("{$monthExpression} as month, COUNT(*) as total")
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month');

        $labels = [];
        $data = [];
        $cursor = $start->copy();

        while ($cursor <= $end) {
            $key = $cursor->format('Y-m');
            $labels[] = $cursor->translatedFormat('M Y');
            $data[] = (int) ($registrations[$key] ?? 0);
            $cursor->addMonth();
        }

        return [
            'datasets' => [
                [
                    'label' => 'Pendaftar',
                    'data' => $data,
                    'fill' => true,
                    'tension' => 0.35,
                ],
            ],
            'labels' => $labels,
        ];
    }
}
