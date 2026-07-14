<?php

declare(strict_types=1);

namespace App\Filament\Resources\PointInjectionBatches\Tables;

use App\Enums\InjectionStatus;
use App\Filament\Resources\PointInjectionBatches\Pages\ViewPointInjectionBatch;
use App\Models\Branch;
use App\Models\PointInjectionDetail;
use App\Models\TransactionType;
use App\Services\Loyalty\BulkInjectionBatchCounterService;
use App\Services\Loyalty\BulkInjectionRowValidator;
use App\Services\Loyalty\RecalculateDetailPointsService;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Carbon;

class PointInjectionDetailsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->query(fn () => PointInjectionDetail::query()->with(['transactionType', 'member.user', 'batch']))
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
                    })
                    ->tooltip(fn (PointInjectionDetail $record): ?string => $record->status === InjectionStatus::Failed
                        ? $record->error_message
                        : null),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        InjectionStatus::Pending->value => 'Pending',
                        InjectionStatus::Validated->value => 'Tervalidasi',
                        InjectionStatus::Success->value => 'Sukses',
                        InjectionStatus::Failed->value => 'Gagal',
                    ])
                    ->placeholder('Semua status')
                    ->native(false),
            ], layout: FiltersLayout::Hidden)
            ->deferFilters(false)
            ->hiddenFilterIndicators()
            ->actions([
                Action::make('edit')
                    ->label('Edit')
                    ->icon('heroicon-o-pencil-square')
                    ->color('primary')
                    ->modalHeading('Edit Baris Injeksi')
                    ->modalWidth('2xl')
                    ->modalSubmitActionLabel('Simpan & Validasi Ulang')
                    ->visible(fn (PointInjectionDetail $record): bool => in_array($record->status, [
                        InjectionStatus::Failed,
                        InjectionStatus::Validated,
                    ], true) && ! ($record->batch?->resolved ?? false))
                    ->form([
                        TextInput::make('raw_member_number')
                            ->label('Nomor Member')
                            ->required()
                            ->maxLength(50),

                        TextInput::make('receipt_number')
                            ->label('Nomor Struk')
                            ->required()
                            ->maxLength(100),

                        DatePicker::make('transaction_date')
                            ->label('Tanggal Transaksi')
                            ->required()
                            ->maxDate(Carbon::today())
                            ->native(false)
                            ->displayFormat('d/m/Y'),

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
                        'raw_member_number' => $record->raw_member_number,
                        'receipt_number' => $record->receipt_number,
                        'transaction_date' => $record->transaction_date,
                        'purchase_nominal' => (float) $record->purchase_nominal,
                        'transaction_type_id' => $record->transaction_type_id,
                        'raw_branch_code' => $record->raw_branch_code ?: null,
                    ])
                    ->action(function (PointInjectionDetail $record, array $data, ViewPointInjectionBatch $livewire): void {
                        self::applyDetailEdit($record, $data);

                        $livewire->refreshBatch();
                    }),

                Action::make('delete')
                    ->label('Hapus')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Hapus Baris Gagal')
                    ->modalDescription('Baris ini akan dihapus dari batch. Tindakan ini tidak dapat dibatalkan.')
                    ->modalSubmitActionLabel('Ya, Hapus')
                    ->visible(fn (PointInjectionDetail $record): bool => $record->status === InjectionStatus::Failed
                        && ! ($record->batch?->resolved ?? false))
                    ->action(function (PointInjectionDetail $record, ViewPointInjectionBatch $livewire): void {
                        $batch = $record->batch;

                        if ($batch === null) {
                            return;
                        }

                        $record->delete();

                        app(BulkInjectionBatchCounterService::class)->syncFromDetails($batch);

                        Notification::make()
                            ->title('Baris dihapus')
                            ->success()
                            ->send();

                        $livewire->refreshBatch();
                    }),
            ])
            ->searchable()
            ->paginated([10, 25, 50])
            ->defaultSort('row_number');
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function applyDetailEdit(PointInjectionDetail $record, array $data): void
    {
        $record->update([
            'raw_member_number' => trim((string) $data['raw_member_number']),
            'receipt_number' => trim((string) $data['receipt_number']),
            'transaction_date' => $data['transaction_date'],
            'purchase_nominal' => $data['purchase_nominal'],
            'transaction_type_id' => $data['transaction_type_id'],
            'raw_branch_code' => $data['raw_branch_code'] ?? '',
        ]);

        $record->refresh();

        $result = app(BulkInjectionRowValidator::class)->validateDetail($record);

        if ($result->isValid()) {
            $record->update([
                'status' => InjectionStatus::Validated,
                'error_message' => null,
                'transaction_type_id' => $result->transactionTypeId(),
                'transaction_date' => $result->transactionDate(),
                'purchase_nominal' => $result->purchaseNominal(),
                'receipt_number' => $result->receiptNumber(),
                'raw_branch_code' => $result->rawBranchCode(),
            ]);

            Notification::make()
                ->title('Baris tervalidasi')
                ->body('Data berhasil diperbarui dan lolos validasi.')
                ->success()
                ->send();
        } else {
            $record->update([
                'status' => InjectionStatus::Failed,
                'error_message' => $result->errorMessage(),
            ]);

            Notification::make()
                ->title('Baris masih gagal')
                ->body($result->errorMessage() ?? 'Validasi gagal.')
                ->danger()
                ->send();
        }

        $record->refresh();

        app(RecalculateDetailPointsService::class)->recalculate($record);

        $batch = $record->batch;

        if ($batch !== null) {
            app(BulkInjectionBatchCounterService::class)->syncFromDetails($batch);
        }
    }
}
