<?php

declare(strict_types=1);

namespace App\Filament\Resources\Branches\Widgets;

use App\Models\Branch;
use App\Models\RedeemInvoice;
use App\Models\Reward;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class BranchStatsOverviewWidget extends StatsOverviewWidget
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
            Stat::make('Jenis Reward', (string) Reward::query()->count())
                ->description('Reward aktif di sistem')
                ->icon('heroicon-o-gift'),
            Stat::make('Jumlah Cabang', (string) Branch::query()->count())
                ->description('Total cabang terdaftar')
                ->icon('heroicon-o-building-storefront'),
            Stat::make('Redeem Bulan Ini', (string) RedeemInvoice::query()
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count())
                ->description(now()->translatedFormat('F Y'))
                ->icon('heroicon-o-receipt-percent'),
        ];
    }
}
