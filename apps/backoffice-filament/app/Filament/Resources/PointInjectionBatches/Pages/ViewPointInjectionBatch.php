<?php

declare(strict_types=1);

namespace App\Filament\Resources\PointInjectionBatches\Pages;

use App\Filament\Resources\PointInjectionBatches\PointInjectionBatchResource;
use App\Filament\Resources\PointInjectionBatches\Tables\PointInjectionDetailsTable;
use App\Models\PointInjectionBatch;
use App\Models\PointInjectionDetail;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;

class ViewPointInjectionBatch extends ViewRecord implements HasTable
{
    use InteractsWithTable;

    private const STALE_PROCESSING_MINUTES = 10;

    protected static string $resource = PointInjectionBatchResource::class;

    public function getView(): string
    {
        return 'filament.resources.point-injection-batches.view-point-injection-batch';
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('back')
                ->label('Kembali ke List')
                ->icon('heroicon-o-arrow-left')
                ->color('gray')
                ->url(PointInjectionBatchResource::getUrl('index')),
        ];
    }

    public function table(Table $table): Table
    {
        /** @var PointInjectionBatch $record */
        $record = $this->record;

        return PointInjectionDetailsTable::configure($table)
            ->query(fn () => PointInjectionDetail::query()
                ->where('batch_id', $record->id)
                ->with(['transactionType', 'member.user', 'batch']))
            ->heading('Daftar Baris Injeksi')
            ->description('Detail setiap baris dalam batch upload ini.')
            ->headerActions([
                Action::make('process')
                    ->label('Process')
                    ->button()
                    ->color('primary')
                    ->disabled(fn (): bool => ! $this->canProcess())
                    ->tooltip(fn (): ?string => $this->processDisabledReason())
                    ->requiresConfirmation()
                    ->modalHeading('Process Point Injection')
                    ->modalDescription('Apakah Anda yakin ingin memproses point injection untuk batch ini?')
                    ->modalSubmitActionLabel('Ya, Proses')
                    ->action(fn () => Notification::make()
                        ->title('Proses Point Injection berhasil dijalankan (Dummy)')
                        ->success()
                        ->send()),
            ]);
    }

    public function refreshBatch(): void
    {
        /** @var PointInjectionBatch $record */
        $record = $this->record;

        $this->record = $record->fresh();

        if ($this->record !== null) {
            $this->record->loadCount('details');
        }
    }

    public function isProcessing(): bool
    {
        /** @var PointInjectionBatch $batch */
        $batch = $this->record;
        $batch->loadCount('details');

        $detailsCount = (int) $batch->details_count;

        if ($batch->total_rows > 0 && $detailsCount < $batch->total_rows) {
            return true;
        }

        if (
            ! $batch->resolved
            && $batch->total_rows === 0
            && $detailsCount === 0
            && $batch->successful_rows === 0
            && $batch->failed_rows === 0
            && $batch->uploaded_at?->gt(now()->subMinutes(self::STALE_PROCESSING_MINUTES))
        ) {
            return true;
        }

        return false;
    }

    public function isStale(): bool
    {
        /** @var PointInjectionBatch $batch */
        $batch = $this->record;
        $batch->loadCount('details');

        return ! $batch->resolved
            && $batch->total_rows === 0
            && (int) $batch->details_count === 0
            && $batch->successful_rows === 0
            && $batch->failed_rows === 0
            && $batch->uploaded_at?->lte(now()->subMinutes(self::STALE_PROCESSING_MINUTES));
    }

    public function canProcess(): bool
    {
        /** @var PointInjectionBatch $batch */
        $batch = $this->record;

        return ! $this->isProcessing()
            && ! $batch->resolved
            && $batch->total_rows > 0
            && $batch->failed_rows === 0
            && $batch->successful_rows === $batch->total_rows;
    }

    public function processDisabledReason(): ?string
    {
        if ($this->canProcess()) {
            return null;
        }

        /** @var PointInjectionBatch $batch */
        $batch = $this->record;

        if ($batch->resolved) {
            return 'Batch sudah pernah diproses';
        }

        if ($this->isProcessing()) {
            return 'File masih diproses di background';
        }

        if ($this->isStale()) {
            return 'Pemrosesan mungkin gagal — pastikan queue worker berjalan';
        }

        if ($batch->total_rows === 0) {
            return 'Tidak ada baris data';
        }

        if ($batch->failed_rows > 0) {
            return 'Terdapat '.$batch->failed_rows.' baris gagal — perbaiki atau hapus terlebih dahulu';
        }

        if ($batch->successful_rows !== $batch->total_rows) {
            return 'Belum semua baris selesai divalidasi';
        }

        return 'Batch belum siap diproses';
    }

    /**
     * @return array{
     *     is_processing: bool,
     *     total_rows: int,
     *     processed_rows: int,
     *     percent: int|null,
     *     validated_rows: int,
     *     failed_rows: int,
     *     is_stale: bool,
     * }
     */
    public function getProgressStats(): array
    {
        /** @var PointInjectionBatch $batch */
        $batch = $this->record;
        $batch->loadCount('details');

        $processedRows = (int) $batch->details_count;
        $totalRows = (int) $batch->total_rows;

        return [
            'is_processing' => $this->isProcessing(),
            'total_rows' => $totalRows,
            'processed_rows' => $processedRows,
            'percent' => $totalRows > 0
                ? (int) round(($processedRows / $totalRows) * 100)
                : null,
            'validated_rows' => (int) $batch->successful_rows,
            'failed_rows' => (int) $batch->failed_rows,
            'is_stale' => $this->isStale(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function getStats(): array
    {
        /** @var PointInjectionBatch $batch */
        $batch = $this->record;

        $totalPurchaseNominal = $batch->details()->sum('purchase_nominal');
        $totalUniqueMember = $batch->details()->distinct('raw_member_number')->count('raw_member_number');

        return [
            'total_points_injected' => number_format((int) $batch->total_points_injected, 0, ',', '.'),
            'total_purchase_nominal' => 'Rp '.number_format((float) $totalPurchaseNominal, 0, ',', '.'),
            'total_unique_members' => number_format($totalUniqueMember, 0, ',', '.'),
            'uploaded_at' => $batch->uploaded_at?->translatedFormat('d M Y, H:i'),
            'media_file_name' => $batch->media?->file_name,
            'staff_name' => $batch->staff?->user?->full_name ?? '-',
        ];
    }
}
