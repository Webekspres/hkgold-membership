<?php

declare(strict_types=1);

namespace App\Filament\Resources\PointInjectionBatches\Tables;

use App\Models\PointInjectionBatch;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PointInjectionBatchesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('staff.user.full_name')
                    ->label('Nama Staff')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('total_rows')
                    ->label('Total Baris')
                    ->numeric()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('successful_rows')
                    ->label('Baris Sukses')
                    ->numeric()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('failed_rows')
                    ->label('Baris Gagal')
                    ->numeric()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('total_points_injected')
                    ->label('Total Poin')
                    ->numeric(thousandsSeparator: ',')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('media.file_name')
                    ->label('Berkas')
                    ->formatStateUsing(fn (): string => 'Lihat File')
                    ->color('primary')
                    ->icon('heroicon-o-document-text')
                    ->url(fn (PointInjectionBatch $record): string => $record->media?->file_url ?? '#', shouldOpenInNewTab: true),
            ])
            ->actions([
                Action::make('viewBatch')
                    ->label('Lihat')
                    ->icon('heroicon-o-eye')
                    ->color('primary')
                    ->action(fn (PointInjectionBatch $record) => Notification::make()
                        ->title('Detail Batch')
                        ->body("Melihat detail batch ID: {$record->id}")
                        ->info()
                        ->send()),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
