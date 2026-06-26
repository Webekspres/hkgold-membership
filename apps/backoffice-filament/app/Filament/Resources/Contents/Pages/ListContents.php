<?php

declare(strict_types=1);

namespace App\Filament\Resources\Contents\Pages;

use App\Enums\ContentType;
use App\Filament\Resources\Contents\ContentResource;
use App\Models\Content;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\EmbeddedTable;
use Filament\Schemas\Components\RenderHook;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Facades\FilamentView;
use Filament\Tables\View\TablesRenderHook;
use Filament\View\PanelsRenderHook;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;

class ListContents extends ListRecords
{
    protected static string $resource = ContentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Tambah Konten'),
        ];
    }

    public function boot(): void
    {
        FilamentView::registerRenderHook(
            TablesRenderHook::TOOLBAR_START,
            fn (): View => view('filament.resources.contents.tables.tabs-header', [
                'activeTab' => $this->activeTab,
                'tabs' => $this->getContentListTabs(),
            ]),
            static::class,
        );
    }

    public function getDefaultActiveTab(): string|int|null
    {
        return 'news';
    }

    /**
     * @return array<string, Tab>
     */
    public function getTabs(): array
    {
        return [
            'news' => Tab::make('News')
                ->modifyQueryUsing(fn (Builder $query): Builder => $query
                    ->where('type', ContentType::News)
                    ->orderByDesc('created_at'))
                ->badge(Content::query()->where('type', ContentType::News)->count()),
            'event' => Tab::make('Event')
                ->modifyQueryUsing(fn (Builder $query): Builder => $query
                    ->where('type', ContentType::Event)
                    ->orderByDesc('event_date'))
                ->badge(Content::query()->where('type', ContentType::Event)->count()),
        ];
    }

    /**
     * @return array<string, array{label: string, badge: int}>
     */
    protected function getContentListTabs(): array
    {
        return [
            'news' => [
                'label' => 'News',
                'badge' => Content::query()->where('type', ContentType::News)->count(),
            ],
            'event' => [
                'label' => 'Event',
                'badge' => Content::query()->where('type', ContentType::Event)->count(),
            ],
        ];
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                RenderHook::make(PanelsRenderHook::RESOURCE_PAGES_LIST_RECORDS_TABLE_BEFORE),
                EmbeddedTable::make(),
                RenderHook::make(PanelsRenderHook::RESOURCE_PAGES_LIST_RECORDS_TABLE_AFTER),
            ]);
    }
}
