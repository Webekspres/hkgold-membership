<?php

declare(strict_types=1);

namespace App\Filament\Resources\RedeemInvoices\Pages;

use App\Filament\Resources\RedeemInvoices\RedeemInvoiceResource;
use App\Filament\Resources\RedeemInvoices\Widgets\RedeemBranchChartWidget;
use App\Filament\Resources\RedeemInvoices\Widgets\RedeemStatsOverviewWidget;
use App\Filament\Resources\RedeemInvoices\Widgets\RedeemTopRewardsWidget;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListRedeemInvoices extends ListRecords
{
    protected static string $resource = RedeemInvoiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('redeem')
                ->label('Redeem')
                ->button()
                ->goldStyle()
                ->icon('heroicon-o-gift')
                ->action(function (): void {
                    Notification::make()
                        ->title('Coming Soon')
                        ->body('Fitur redeem akan segera tersedia.')
                        ->warning()
                        ->send();
                }),
        ];
    }

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
