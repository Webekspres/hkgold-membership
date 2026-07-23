<?php

declare(strict_types=1);

namespace App\Filament\Resources\SubDistricts\Tables;

use App\Models\Province;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SubDistrictsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nama')
                    ->label('Nama Kecamatan')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('city.nama')
                    ->label('Kota/Kabupaten')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('city.province.nama')
                    ->label('Provinsi')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('villages_count')
                    ->label('Jumlah Kelurahan')
                    ->counts('villages')
                    ->sortable(),
            ])
            ->defaultSort('nama')
            ->filters([
                SelectFilter::make('province_id')
                    ->label('Provinsi')
                    ->options(fn (): array => Province::query()->orderBy('nama')->pluck('nama', 'id')->all())
                    ->searchable()
                    ->query(fn (Builder $query, array $data): Builder => $query->when(
                        filled($data['value'] ?? null),
                        fn (Builder $query): Builder => $query->whereHas(
                            'city',
                            fn (Builder $query): Builder => $query->where('province_id', $data['value']),
                        ),
                    )),

                SelectFilter::make('city_id')
                    ->label('Kota/Kabupaten')
                    ->relationship('city', 'nama')
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
