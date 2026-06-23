<?php

declare(strict_types=1);

namespace App\Filament\Resources\Villages\Tables;

use App\Models\Province;
use App\Models\Regency;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class VillagesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nama')
                    ->label('Nama Kelurahan')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('district.nama')
                    ->label('Kecamatan')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('district.regency.nama')
                    ->label('Kota/Kabupaten')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('district.regency.province.nama')
                    ->label('Provinsi')
                    ->searchable()
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
                            'district.regency',
                            fn (Builder $query): Builder => $query->where('province_id', $data['value']),
                        ),
                    )),

                SelectFilter::make('regency_id')
                    ->label('Kota/Kabupaten')
                    ->options(fn (): array => Regency::query()->orderBy('nama')->pluck('nama', 'id')->all())
                    ->searchable()
                    ->query(fn (Builder $query, array $data): Builder => $query->when(
                        filled($data['value'] ?? null),
                        fn (Builder $query): Builder => $query->whereHas(
                            'district',
                            fn (Builder $query): Builder => $query->where('city_id', $data['value']),
                        ),
                    )),

                SelectFilter::make('district_id')
                    ->label('Kecamatan')
                    ->relationship('district', 'nama')
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
