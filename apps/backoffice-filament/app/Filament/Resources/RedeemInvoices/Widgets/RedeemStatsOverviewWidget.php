<?php

declare(strict_types=1);

namespace App\Filament\Resources\RedeemInvoices\Widgets;

use App\Enums\RedeemStatus;
use App\Models\RedeemInvoice;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class RedeemStatsOverviewWidget extends StatsOverviewWidget
{
    protected int|string|array $columnSpan = 'full';

    protected int|array|null $columns = 2;

    /**
     * @return array<Stat>
     */
    protected function getStats(): array
    {
        $lastThirtyDays = now()->subDays(30);
        $today = today();

        return [
            Stat::make(
                'Total Poin Ditukarkan (30 Hari)',
                number_format((int) RedeemInvoice::query()
                    ->where('created_at', '>=', $lastThirtyDays)
                    ->sum('points_redeemed'), 0, ',', '.'),
            )
                ->icon('heroicon-o-gift')
                ->description('Akumulasi poin redeem 30 hari terakhir'),
            Stat::make(
                'Klaim Sukses Hari Ini',
                (string) RedeemInvoice::query()
                    ->whereDate('created_at', $today)
                    ->where('status', RedeemStatus::Completed->value)
                    ->count(),
            )
                ->icon('heroicon-o-check-circle')
                ->description('Total invoice berhasil ditukar hari ini'),
        ];
    }
}
