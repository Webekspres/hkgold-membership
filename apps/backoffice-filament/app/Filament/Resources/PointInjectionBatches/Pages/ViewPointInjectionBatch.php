<?php

declare(strict_types=1);

namespace App\Filament\Resources\PointInjectionBatches\Pages;

use App\Enums\InjectionStatus;
use App\Exceptions\Loyalty\ProcessBatchException;
use App\Filament\Resources\PointInjectionBatches\Actions\RetryBulkImportAction;
use App\Filament\Resources\PointInjectionBatches\PointInjectionBatchResource;
use App\Filament\Resources\PointInjectionBatches\Support\ProcessBatchSummarySupport;
use App\Filament\Resources\PointInjectionBatches\Tables\PointInjectionDetailsTable;
use App\Jobs\ProcessPointInjectionBatchJob;
use App\Models\PointInjectionBatch;
use App\Models\PointInjectionDetail;
use App\Services\Loyalty\ProcessBatchService;
use Filament\Actions\Action;
use Filament\Forms\Components\Placeholder;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Enums\Width;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;

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
            RetryBulkImportAction::make(),
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
            ->query(fn() => PointInjectionDetail::query()
                ->where('batch_id', $record->id)
                ->with(['transactionType', 'member.user', 'batch']))
            ->heading('Daftar Baris Injeksi')
            ->description('Detail setiap baris dalam batch upload ini.')
            ->headerActions([
                Action::make('process')
                    ->label('Process')
                    ->button()
                    ->goldStyle()
                    ->color('primary')
                    ->disabled(fn(): bool => ! $this->canProcess())
                    ->visible(fn(): bool => ! $this->record->resolved)
                    ->tooltip(fn(): ?string => $this->processDisabledReason())
                    ->modalHeading('Proses Injeksi Poin')
                    ->modalDescription('Periksa ringkasan berikut sebelum memproses batch ke PointMutation.')
                    ->modalWidth(Width::TwoExtraLarge)
                    ->modalSubmitActionLabel('Ya, Proses Sekarang')
                    ->form([
                        Placeholder::make('summary')
                            ->label('Ringkasan')
                            ->content(function (): HtmlString {
                                $summary = app(ProcessBatchService::class)->buildSummary($this->record);

                                return new HtmlString(ProcessBatchSummarySupport::buildHtml($summary));
                            }),
                    ])
                    ->action(function (): void {
                        /** @var PointInjectionBatch $batch */
                        $batch = $this->record;

                        try {
                            app(ProcessBatchService::class)->assertBatchCanProcess($batch);
                        } catch (ProcessBatchException $exception) {
                            Notification::make()
                                ->title('Batch belum siap diproses')
                                ->body($exception->getMessage())
                                ->danger()
                                ->send();

                            return;
                        }

                        $actor = Auth::user();

                        if ($actor === null) {
                            Notification::make()
                                ->title('Autentikasi gagal')
                                ->body('Silakan login ulang.')
                                ->danger()
                                ->send();

                            return;
                        }

                        $batch->update(['processing_started_at' => now()]);

                        ProcessPointInjectionBatchJob::dispatch(
                            $batch->fresh(),
                            $actor,
                            request()->ip() ?? '127.0.0.1',
                        );

                        $this->refreshBatch();

                        Notification::make()
                            ->title('Batch sedang diproses')
                            ->body('Injeksi poin berjalan di background. Halaman akan diperbarui otomatis.')
                            ->success()
                            ->send();
                    }),
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
        if ($this->isFinalizing()) {
            return false;
        }

        /** @var PointInjectionBatch $batch */
        $batch = $this->record;
        $batch->loadCount('details');

        $detailsCount = (int) $batch->details_count;

        if ($batch->import_started_at !== null && $batch->total_rows > 0 && $detailsCount < $batch->total_rows) {
            return true;
        }

        if ($batch->total_rows > 0 && $detailsCount < $batch->total_rows) {
            return true;
        }

        if (
            ! $batch->resolved
            && $batch->total_rows === 0
            && $detailsCount === 0
            && $batch->successful_rows === 0
            && $batch->failed_rows === 0
            && $batch->import_started_at !== null
        ) {
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

    public function isFinalizing(): bool
    {
        /** @var PointInjectionBatch $batch */
        $batch = $this->record;

        return $batch->processing_started_at !== null && ! $batch->resolved;
    }

    public function isStale(): bool
    {
        return RetryBulkImportAction::isStale($this->record);
    }

    public function canRetryImport(): bool
    {
        return RetryBulkImportAction::canRetry($this->record);
    }

    public function canProcess(): bool
    {
        /** @var PointInjectionBatch $batch */
        $batch = $this->record;

        if ($batch->resolved || $this->isFinalizing() || $this->isProcessing()) {
            return false;
        }

        try {
            app(ProcessBatchService::class)->assertBatchCanProcess($batch);

            return true;
        } catch (ProcessBatchException) {
            return false;
        }
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

        if ($this->isFinalizing()) {
            return 'Batch sedang diproses ke PointMutation';
        }

        if ($this->isProcessing()) {
            return 'File masih diproses di background';
        }

        if ($this->isStale()) {
            return 'Pemrosesan mungkin gagal — gunakan Ulangi Parsing atau pastikan queue worker berjalan';
        }

        try {
            app(ProcessBatchService::class)->assertBatchCanProcess($batch);
        } catch (ProcessBatchException $exception) {
            return $exception->getMessage();
        }

        return 'Batch belum siap diproses';
    }

    /**
     * @return array{
     *     is_processing: bool,
     *     is_finalizing: bool,
     *     is_resolved: bool,
     *     total_rows: int,
     *     processed_rows: int,
     *     percent: int|null,
     *     validated_rows: int,
     *     failed_rows: int,
     *     success_rows: int,
     *     finalize_percent: int|null,
     *     is_stale: bool,
     *     can_retry_import: bool,
     * }
     */
    public function getProgressStats(): array
    {
        /** @var PointInjectionBatch $batch */
        $batch = $this->record;
        $batch->loadCount('details');

        $processedRows = (int) $batch->details_count;
        $totalRows = (int) $batch->total_rows;
        $successRows = $batch->details()->where('status', InjectionStatus::Success)->count();
        $validatedRows = (int) $batch->successful_rows;

        return [
            'is_processing' => $this->isProcessing(),
            'is_finalizing' => $this->isFinalizing(),
            'is_resolved' => (bool) $batch->resolved,
            'total_rows' => $totalRows,
            'processed_rows' => $processedRows,
            'percent' => $totalRows > 0
                ? (int) round(($processedRows / $totalRows) * 100)
                : null,
            'validated_rows' => $validatedRows,
            'failed_rows' => (int) $batch->failed_rows,
            'success_rows' => $successRows,
            'finalize_percent' => $validatedRows > 0
                ? (int) round(($successRows / $validatedRows) * 100)
                : null,
            'is_stale' => $this->isStale(),
            'can_retry_import' => $this->canRetryImport(),
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
            'total_purchase_nominal' => 'Rp ' . number_format((float) $totalPurchaseNominal, 0, ',', '.'),
            'total_unique_members' => number_format($totalUniqueMember, 0, ',', '.'),
            'uploaded_at' => $batch->uploaded_at?->translatedFormat('d M Y, H:i'),
            'media_file_name' => $batch->media?->file_name,
            'staff_name' => $batch->staff?->user?->full_name ?? '-',
        ];
    }
}
