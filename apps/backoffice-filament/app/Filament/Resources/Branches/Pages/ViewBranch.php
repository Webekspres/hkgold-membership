<?php

declare(strict_types=1);

namespace App\Filament\Resources\Branches\Pages;

use App\Filament\Resources\Branches\BranchResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\View;

class ViewBranch extends ViewRecord
{
    protected static string $resource = BranchResource::class;

    /**
     * @var array<int, string>
     */
    public array $mountedRelationManagers = [];

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }

    public function mount(int|string $record): void
    {
        parent::mount($record);

        $this->initializeRelationManagerTabs();
    }

    protected function initializeRelationManagerTabs(): void
    {
        $managers = $this->getRelationManagers();

        if (! array_key_exists($this->activeRelationManager ?? '', $managers)) {
            $this->activeRelationManager = array_key_first($managers);
        }

        $this->mountRelationManagerTab($this->activeRelationManager);
    }

    public function switchRelationTab(string $tab): void
    {
        if (! array_key_exists($tab, $this->getRelationManagers())) {
            return;
        }

        $this->activeRelationManager = $tab;
        $this->mountRelationManagerTab($tab);
    }

    protected function mountRelationManagerTab(?string $tab): void
    {
        if (blank($tab)) {
            return;
        }

        if (in_array($tab, $this->mountedRelationManagers, true)) {
            return;
        }

        $this->mountedRelationManagers[] = $tab;
    }

    /**
     * @return array<string, array{label: string}>
     */
    public static function getRelationManagerTabDefinitions(): array
    {
        return [
            'reward-stocks' => ['label' => 'Stock Reward'],
            'redeem-invoices' => ['label' => 'Riwayat Redeem'],
            'activity-logs' => ['label' => 'Riwayat Aktivitas'],
        ];
    }

    public function getRelationManagersContentComponent(): Component
    {
        return View::make('filament.resources.branches.partials.relation-managers-container')
            ->viewData(fn (): array => [
                'managers' => $this->getRelationManagers(),
                'mountedManagers' => $this->mountedRelationManagers,
                'activeManager' => $this->activeRelationManager,
                'ownerRecord' => $this->getRecord(),
                'pageClass' => static::class,
            ]);
    }
}
