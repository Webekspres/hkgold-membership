<?php

declare(strict_types=1);

namespace App\Filament\Resources\Members\Pages;

use App\Filament\Resources\Members\MemberResource;
use App\Filament\Resources\Members\Widgets\MemberRegistrationChartWidget;
use App\Filament\Resources\Members\Widgets\MemberTierDistributionChartWidget;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMembers extends ListRecords
{
    protected static string $resource = MemberResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Tambah Member')
                ->color('primary')
                ->extraAttributes([
                    'style' => 'background: linear-gradient(135deg, #f5c842, #e8a020); border: none;',
                    'class' => 'text-black font-bold hover:opacity-90 shadow-lg',
                ]),
        ];
    }

    /**
     * @return array<class-string>
     */
    public function getHeaderWidgets(): array
    {
        return [
            MemberRegistrationChartWidget::class,
            MemberTierDistributionChartWidget::class,
        ];
    }

    protected function getColumns(): int|string|array
    {
        return [
            'md' => 2,
            'lg' => 3,
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
