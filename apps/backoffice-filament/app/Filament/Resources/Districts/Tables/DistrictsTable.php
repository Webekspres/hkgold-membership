<?php

declare(strict_types=1);

namespace App\Filament\Resources\Districts\Tables;

use App\Models\Province;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class DistrictsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama Kecamatan')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('regency.name')
                    ->label('Kota/Kabupaten')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('regency.province.name')
                    ->label('Provinsi')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('villages_count')
                    ->label('Jumlah Kelurahan')
                    ->counts('villages')
                    ->sortable(),
            ])
            ->defaultSort('name')
            ->filters([
                SelectFilter::make('province_id')
                    ->label('Provinsi')
                    ->options(fn (): array => Province::query()->orderBy('name')->pluck('name', 'id')->all())
                    ->searchable()
                    ->query(fn (Builder $query, array $data): Builder => $query->when(
                        filled($data['value'] ?? null),
                        fn (Builder $query): Builder => $query->whereHas(
                            'regency',
                            fn (Builder $query): Builder => $query->where('province_id', $data['value']),
                        ),
                    )),

                SelectFilter::make('regency_id')
                    ->label('Kota/Kabupaten')
                    ->relationship('regency', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                ActionGroup::make([
                    EditAction::make(),
                    DeleteAction::make(),
                ]),
            ]);
    }
}
