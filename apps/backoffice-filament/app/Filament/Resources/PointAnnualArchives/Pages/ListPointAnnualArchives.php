<?php

declare(strict_types=1);

namespace App\Filament\Resources\PointAnnualArchives\Pages;

use App\Filament\Resources\PointAnnualArchives\Actions\ArchivePointsAction;
use App\Filament\Resources\PointAnnualArchives\PointAnnualArchiveResource;
use App\Filament\Resources\PointAnnualArchives\Widgets\PeriodFrozenPointsChartWidget;
use App\Filament\Resources\PointAnnualArchives\Widgets\PeriodTierDistributionChartWidget;
use App\Filament\Resources\PointAnnualArchives\Widgets\PeriodTotalMembersChartWidget;
use Filament\Resources\Pages\ListRecords;

class ListPointAnnualArchives extends ListRecords
{
    protected static string $resource = PointAnnualArchiveResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ArchivePointsAction::make(),
        ];
    }

    /**
     * @return array<class-string>
     */
    public function getHeaderWidgets(): array
    {
        return [
            PeriodTotalMembersChartWidget::class,
            PeriodFrozenPointsChartWidget::class,
            PeriodTierDistributionChartWidget::class,
        ];
    }

    /**
     * @return int|array<string, int|null>
     */
    public function getHeaderWidgetsColumns(): int|array
    {
        return [
            'default' => 1,
            'md' => 2,
            'xl' => 2,
        ];
    }
}
