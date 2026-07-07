<?php

declare(strict_types=1);

namespace App\Filament\Resources\PointInjectionBatches\Tables;

use App\Filament\Resources\PointInjectionBatches\PointInjectionBatchResource;
use App\Models\PointInjectionBatch;
use Filament\Actions\Action;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
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

                IconColumn::make('resolved')
                    ->label('Status')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-clock')
                    ->trueColor('success')
                    ->falseColor('warning')
                    ->tooltip(fn (PointInjectionBatch $record): string => $record->resolved ? 'Selesai' : 'Belum Diselesaikan'),
            ])
            ->filters([
                TernaryFilter::make('resolved')
                    ->label('Status Penyelesaian')
                    ->trueLabel('Selesai')
                    ->falseLabel('Belum Diselesaikan')
                    ->native(false),
            ])
            ->actions([
                Action::make('viewBatch')
                    ->label('Lihat')
                    ->icon('heroicon-o-eye')
                    ->color('primary')
                    ->url(fn (PointInjectionBatch $record): string => PointInjectionBatchResource::getUrl('view', ['record' => $record->id])),
            ])
            ->bulkActions([]);
    }
}
