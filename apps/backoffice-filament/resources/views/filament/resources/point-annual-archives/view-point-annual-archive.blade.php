<x-filament-panels::page>
    <style>
        .period-archive-view {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        /* Period Details Header (Top Section) */
        .period-header-container {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding: 1rem 1.5rem;
            background-color: rgb(255 255 255);
            border: 1px solid rgb(229 231 235);
            border-radius: 0.75rem;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
        }

        .period-header-left {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }

        .period-header-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: rgb(17 24 39);
        }

        .period-header-year {
            font-size: 0.875rem;
            color: rgb(107 114 128);
        }

        .period-header-right {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 0.25rem;
        }

        .period-header-label {
            font-size: 0.75rem;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: rgb(156 163 175);
        }

        .period-header-value {
            font-size: 0.875rem;
            font-weight: 600;
            color: rgb(75 85 99);
        }

        /* 2-Column Middle Grid */
        .period-middle-grid {
            display: grid;
            grid-template-columns: repeat(1, minmax(0, 1fr));
            gap: 0.75rem;
        }

        @media (min-width: 1024px) {
            .period-middle-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        /* Stats Cards Grid (2x2 on large screens) */
        .period-stats-grid {
            display: grid;
            grid-template-columns: repeat(1, minmax(0, 1fr));
            gap: 0.75rem;
        }

        @media (min-width: 768px) {
            .period-stats-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        .period-stat-card {
            padding: 1.5rem;
            background-color: rgb(255 255 255);
            border: 1px solid rgb(229 231 235);
            border-radius: 0.75rem;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            transition: all 0.2s ease-in-out;
        }

        .period-stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
        }

        .period-stat-label {
            font-size: 0.875rem;
            font-weight: 500;
            color: rgb(107 114 128);
        }

        .period-stat-value {
            font-size: 1.5rem;
            font-weight: 700;
            letter-spacing: -0.025em;
            color: rgb(17 24 39);
            margin-top: 0.25rem;
        }

        .period-stat-growth {
            margin-top: 0.5rem;
            font-size: 0.75rem;
            color: rgb(156 163 175);
        }

        .growth-up {
            color: rgb(22 163 74);
            font-weight: 600;
        }

        .growth-down {
            color: rgb(220 38 38);
            font-weight: 600;
        }

        .growth-none {
            color: rgb(156 163 175);
        }

        /* Pie Chart Column (Right) */
        .period-chart-column {
            min-height: 320px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        /* Dark Mode Support */
        .dark .period-header-container,
        .dark .period-stat-card {
            border-color: var(--gray-800, rgb(39 39 42));
            background-color: var(--gray-900, rgb(24 24 27));
        }

        .dark .period-header-title,
        .dark .period-stat-value {
            color: white;
        }

        .dark .period-header-year,
        .dark .period-stat-label,
        .dark .period-header-value {
            color: var(--gray-400, rgb(161 161 170));
        }

        .dark .growth-up {
            color: rgb(74 222 128);
        }

        .dark .growth-down {
            color: rgb(248 113 113);
        }
    </style>

    @php
        $stats = $this->getStats();
    @endphp

    <div class="period-archive-view">
        <!-- 1. Header Info Row -->
        <div class="period-header-container">
            <div class="period-header-left">
                <h2 class="period-header-title">{{ $stats['name'] }}</h2>
                <span class="period-header-year">Tahun Arsip: {{ $stats['archive_year'] }}</span>
            </div>
            <div class="period-header-right">
                <span class="period-header-label">Waktu Diarsipkan</span>
                <span class="period-header-value">{{ $stats['archived_at'] }}</span>
            </div>
        </div>

        <!-- 2. Middle Row: 2 Columns Layout -->
        <div class="period-middle-grid">
            <!-- Left: Stats 2x2 Grid -->
            <div class="period-stats-grid">
                <div class="period-stat-card">
                    <div>
                        <span class="period-stat-label">Total Member</span>
                        <h3 class="period-stat-value">{{ $stats['total_members'] }}</h3>
                    </div>
                    <div class="period-stat-growth">
                        @if ($stats['members_growth'] === null)
                            <span class="growth-none">Tidak ada data pembanding</span>
                        @elseif ($stats['members_growth'] >= 0)
                            <span class="growth-up">▲ +{{ number_format($stats['members_growth'], 1) }}%</span> dibandingkan periode sebelumnya
                        @else
                            <span class="growth-down">▼ {{ number_format($stats['members_growth'], 1) }}%</span> dibandingkan periode sebelumnya
                        @endif
                    </div>
                </div>

                <div class="period-stat-card">
                    <div>
                        <span class="period-stat-label">Total Poin Didapatkan</span>
                        <h3 class="period-stat-value">{{ $stats['earned_points_total'] }}</h3>
                    </div>
                    <div class="period-stat-growth">
                        @if ($stats['earned_growth'] === null)
                            <span class="growth-none">Tidak ada data pembanding</span>
                        @elseif ($stats['earned_growth'] >= 0)
                            <span class="growth-up">▲ +{{ number_format($stats['earned_growth'], 1) }}%</span> dibandingkan periode sebelumnya
                        @else
                            <span class="growth-down">▼ {{ number_format($stats['earned_growth'], 1) }}%</span> dibandingkan periode sebelumnya
                        @endif
                    </div>
                </div>

                <div class="period-stat-card">
                    <div>
                        <span class="period-stat-label">Total Poin Dibekukan</span>
                        <h3 class="period-stat-value">{{ $stats['frozen_points_total'] }}</h3>
                    </div>
                    <div class="period-stat-growth">
                        @if ($stats['frozen_growth'] === null)
                            <span class="growth-none">Tidak ada data pembanding</span>
                        @elseif ($stats['frozen_growth'] >= 0)
                            <span class="growth-up">▲ +{{ number_format($stats['frozen_growth'], 1) }}%</span> dibandingkan periode sebelumnya
                        @else
                            <span class="growth-down">▼ {{ number_format($stats['frozen_growth'], 1) }}%</span> dibandingkan periode sebelumnya
                        @endif
                    </div>
                </div>

                <div class="period-stat-card">
                    <div>
                        <span class="period-stat-label">Total Poin Ditukarkan</span>
                        <h3 class="period-stat-value">{{ $stats['redeemed_points_total'] }}</h3>
                    </div>
                    <div class="period-stat-growth">
                        @if ($stats['redeemed_growth'] === null)
                            <span class="growth-none">Tidak ada data pembanding</span>
                        @elseif ($stats['redeemed_growth'] >= 0)
                            <span class="growth-up">▲ +{{ number_format($stats['redeemed_growth'], 1) }}%</span> dibandingkan periode sebelumnya
                        @else
                            <span class="growth-down">▼ {{ number_format($stats['redeemed_growth'], 1) }}%</span> dibandingkan periode sebelumnya
                        @endif
                    </div>
                </div>
            </div>

            <!-- Right: Pie Chart Component -->
            <div class="period-chart-column">
                @livewire(\App\Filament\Resources\PointAnnualArchives\Widgets\PeriodDetailTierChartWidget::class, ['record' => $this->record])
            </div>
        </div>

        <!-- 3. Bottom Row: Detail Table -->
        <div>
            {{ $this->table }}
        </div>
    </div>
</x-filament-panels::page>
