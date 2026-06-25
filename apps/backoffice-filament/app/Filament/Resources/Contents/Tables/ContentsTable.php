<?php

declare(strict_types=1);

namespace App\Filament\Resources\Contents\Tables;

use App\Enums\ContentType;
use App\Filament\Resources\Contents\Pages\ListContents;
use App\Models\Content;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class ContentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('Judul')
                    ->searchable()
                    ->sortable(),

                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),

                TextColumn::make('tanggal')
                    ->label('Tanggal')
                    ->state(fn (Content $record): ?Carbon => $record->type === ContentType::Event
                        ? $record->event_date
                        : $record->created_at)
                    ->dateTime()
                    ->sortable(query: function (Builder $query, string $direction, ListContents $livewire): Builder {
                        $column = $livewire->activeTab === 'event' ? 'event_date' : 'created_at';

                        return $query->orderBy($column, $direction);
                    }),
            ])
            ->recordActions([
                EditAction::make(),
            ]);
    }
}
