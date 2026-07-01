<?php

declare(strict_types=1);

namespace App\Filament\Resources\PointMutations\Widgets;

use App\Filament\Resources\PointMutations\Support\PointMutationSupport;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PointMutationStatsOverviewWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = 'full';

    protected int|array|null $columns = 3;

    /**
     * @return array<Stat>
     */
    protected function getStats(): array
    {
        return [
            Stat::make('Rerata Poin per Member', PointMutationSupport::formatNumber(PointMutationSupport::averagePointsPerMember()))
                ->description('Member aktif saat ini')
                ->icon('heroicon-o-users'),
            Stat::make('Poin Terbit', PointMutationSupport::formatNumber(PointMutationSupport::pointsIssuedLastSevenDays()))
                ->description('7 hari terakhir')
                ->icon('heroicon-o-arrow-trending-up'),
            Stat::make('Poin Redeem', PointMutationSupport::formatNumber(PointMutationSupport::pointsRedeemedLastSevenDays()))
                ->description('7 hari terakhir')
                ->icon('heroicon-o-arrow-trending-down'),
        ];
    }
}
