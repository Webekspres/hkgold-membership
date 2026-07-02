<?php

declare(strict_types=1);

namespace App\Filament\Resources\PointInjectionBatches\Tables;

use App\Enums\InjectionStatus;
use App\Models\Branch;
use App\Models\PointInjectionDetail;
use App\Models\TransactionType;
use App\Services\Loyalty\RecalculateDetailPointsService;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PointInjectionDetailsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->query(fn () => PointInjectionDetail::query()->with(['transactionType', 'member.user']))
            ->columns([
                TextColumn::make('row_number')
                    ->label('No. Baris')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('raw_member_number')
                    ->label('Kode Member')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('member.user.full_name')
                    ->label('Nama Member')
                    ->searchable()
                    ->toggleable()
                    ->default('-'),

                TextColumn::make('member.current_tier')
                    ->label('Tier')
                    ->badge()
                    ->color(fn ($state) => match ($state?->value ?? '') {
                        'SILVER' => 'gray',
                        'GOLD' => 'warning',
                        'PLATINUM' => 'info',
                        'SAPPHIRE' => 'primary',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn ($state) => $state?->value ?? '-')
                    ->toggleable(),

                TextColumn::make('raw_branch_code')
                    ->label('Cabang')
                    ->searchable()
                    ->toggleable()
                    ->default('-'),

                TextColumn::make('transactionType.display_name')
                    ->label('Jenis Transaksi')
                    ->toggleable(),

                TextColumn::make('transaction_date')
                    ->label('Tgl Transaksi')
                    ->date('d/m/Y')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('purchase_nominal')
                    ->label('Nominal Pembelian')
                    ->money('IDR', locale: 'id')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('calculated_points')
                    ->label('Poin Dikalkulasi')
                    ->numeric(thousandsSeparator: ',')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (InjectionStatus $state): string => match ($state) {
                        InjectionStatus::Pending => 'gray',
                        InjectionStatus::Validated => 'info',
                        InjectionStatus::Success => 'success',
                        InjectionStatus::Failed => 'danger',
                    })
                    ->formatStateUsing(fn (InjectionStatus $state): string => match ($state) {
                        InjectionStatus::Pending => 'Pending',
                        InjectionStatus::Validated => 'Tervalidasi',
                        InjectionStatus::Success => 'Sukses',
                        InjectionStatus::Failed => 'Gagal',
                    }),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        InjectionStatus::Pending->value => 'Pending',
                        InjectionStatus::Validated->value => 'Tervalidasi',
                        InjectionStatus::Success->value => 'Sukses',
                        InjectionStatus::Failed->value => 'Gagal',
                    ]),
            ])
            ->actions([
                Action::make('edit')
                    ->label('Edit')
                    ->icon('heroicon-o-pencil-square')
                    ->color('primary')
                    ->modalHeading('Edit Baris Injeksi')
                    ->modalWidth('2xl')
                    ->modalSubmitActionLabel('Simpan & Hitung Ulang Poin')
                    ->form([
                        TextInput::make('purchase_nominal')
                            ->label('Nominal Pembelian')
                            ->numeric()
                            ->prefix('Rp')
                            ->required()
                            ->minValue(1),

                        Select::make('transaction_type_id')
                            ->label('Jenis Transaksi')
                            ->options(fn () => TransactionType::query()->pluck('display_name', 'id'))
                            ->searchable()
                            ->required(),

                        Select::make('raw_branch_code')
                            ->label('Cabang (Referensi)')
                            ->options(fn () => Branch::query()->pluck('branch_code', 'branch_code'))
                            ->searchable()
                            ->nullable()
                            ->placeholder('Pilih cabang...'),
                    ])
                    ->fillForm(fn (PointInjectionDetail $record): array => [
                        'purchase_nominal' => (float) $record->purchase_nominal,
                        'transaction_type_id' => $record->transaction_type_id,
                        'raw_branch_code' => $record->raw_branch_code ?: null,
                    ])
                    ->action(function (PointInjectionDetail $record, array $data): void {
                        $record->update([
                            'purchase_nominal' => $data['purchase_nominal'],
                            'transaction_type_id' => $data['transaction_type_id'],
                            'raw_branch_code' => $data['raw_branch_code'] ?? '',
                        ]);

                        app(RecalculateDetailPointsService::class)->recalculate($record);
                    }),
            ])
            ->searchable()
            ->paginated([10, 25, 50])
            ->defaultSort('row_number');
    }
}
