<?php

declare(strict_types=1);

namespace App\Filament\Resources\PointMutations\Pages;

use App\Filament\Resources\PointMutations\PointMutationResource;
use App\Filament\Resources\PointMutations\Widgets\PointMutationStatsOverviewWidget;
use Filament\Resources\Pages\ListRecords;

class ListPointMutations extends ListRecords
{
    protected static string $resource = PointMutationResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    /**
     * @return array<class-string>
     */
    public function getHeaderWidgets(): array
    {
        return [
            PointMutationStatsOverviewWidget::class,
        ];
    }

    /**
     * @return int|array<string, int|null>
     */
    public function getHeaderWidgetsColumns(): int|array
    {
        return [
            'default' => 1,
            'md' => 3,
            'xl' => 3,
        ];
    }
}
