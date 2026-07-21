<?php

declare(strict_types=1);

namespace App\Filament\Resources\RedeemInvoices\Pages;

use App\Filament\Resources\RedeemInvoices\RedeemInvoiceResource;
use App\Filament\Resources\RedeemInvoices\Widgets\RedeemBranchChartWidget;
use App\Filament\Resources\RedeemInvoices\Widgets\RedeemStatsOverviewWidget;
use App\Filament\Resources\RedeemInvoices\Widgets\RedeemTopRewardsWidget;
use Filament\Resources\Pages\ListRecords;

class ListRedeemInvoices extends ListRecords
{
    protected static string $resource = RedeemInvoiceResource::class;

    /**
     * @return array<class-string>
     */
    public function getHeaderWidgets(): array
    {
        return [
            RedeemStatsOverviewWidget::class,
            RedeemBranchChartWidget::class,
            RedeemTopRewardsWidget::class,
        ];
    }

    /**
     * @return int|array<string, int|null>
     */
    public function getHeaderWidgetsColumns(): int|array
    {
        return [
            'default' => 1,
            'lg' => 5,
        ];
    }
}
