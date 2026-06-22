<?php

declare(strict_types=1);

namespace App\Filament\Resources\PostalCodes\Tables;

use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PostalCodesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label('Kode Pos')
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                TextColumn::make('addresses_count')
                    ->label('Jumlah Alamat')
                    ->counts('addresses')
                    ->sortable(),
            ])
            ->defaultSort('code')
            ->filters([])
            ->recordActions([
                ActionGroup::make([
                    EditAction::make(),
                    DeleteAction::make(),
                ]),
            ]);
    }
}
