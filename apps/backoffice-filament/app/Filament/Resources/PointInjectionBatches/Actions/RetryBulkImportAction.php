<?php

declare(strict_types=1);

namespace App\Filament\Resources\PointInjectionBatches\Actions;

use App\Filament\Resources\PointInjectionBatches\Pages\ViewPointInjectionBatch;
use App\Jobs\ProcessBulkInjectionJob;
use App\Models\PointInjectionBatch;
use App\Models\PointInjectionDetail;
use Filament\Actions\Action;
use Filament\Notifications\Notification;

class RetryBulkImportAction
{
    public static function make(): Action
    {
        return Action::make('retryImport')
            ->label('Ulangi Parsing')
            ->icon('heroicon-o-arrow-path')
            ->color('warning')
            ->requiresConfirmation()
            ->modalHeading('Ulangi Parsing File')
            ->modalDescription('Semua baris detail yang sudah diparsing akan dihapus dan file akan diproses ulang dari awal.')
            ->modalSubmitActionLabel('Ya, Ulangi')
            ->visible(fn (ViewPointInjectionBatch $livewire): bool => self::canRetry($livewire->record))
            ->action(function (ViewPointInjectionBatch $livewire): void {
                /** @var PointInjectionBatch $record */
                $record = $livewire->record;

                if (! self::canRetry($record)) {
                    Notification::make()
                        ->title('Tidak dapat mengulangi parsing')
                        ->body('Batch tidak memenuhi syarat untuk diulang.')
                        ->danger()
                        ->send();

                    return;
                }

                PointInjectionDetail::query()
                    ->where('batch_id', $record->id)
                    ->delete();

                $record->update([
                    'total_rows' => 0,
                    'successful_rows' => 0,
                    'failed_rows' => 0,
                    'import_started_at' => now(),
                ]);

                ProcessBulkInjectionJob::dispatch($record->fresh());

                $livewire->refreshBatch();

                Notification::make()
                    ->title('Parsing dimulai ulang')
                    ->body('File sedang diproses di background.')
                    ->success()
                    ->send();
            });
    }

    public static function canRetry(PointInjectionBatch $batch): bool
    {
        if ($batch->resolved) {
            return false;
        }

        if ($batch->processing_started_at !== null) {
            return false;
        }

        $batch->loadCount('details');
        $detailsCount = (int) $batch->details_count;

        if ($batch->total_rows > 0 && $detailsCount < $batch->total_rows) {
            return true;
        }

        return self::isStale($batch);
    }

    public static function isStale(PointInjectionBatch $batch): bool
    {
        $batch->loadCount('details');

        return ! $batch->resolved
            && $batch->total_rows === 0
            && (int) $batch->details_count === 0
            && $batch->successful_rows === 0
            && $batch->failed_rows === 0
            && $batch->uploaded_at?->lte(now()->subMinutes(10));
    }
}
