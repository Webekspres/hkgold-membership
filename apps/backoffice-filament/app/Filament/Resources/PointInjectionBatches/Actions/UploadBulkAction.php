<?php

declare(strict_types=1);

namespace App\Filament\Resources\PointInjectionBatches\Actions;

use App\Filament\Resources\PointInjectionBatches\PointInjectionBatchResource;
use App\Filament\Resources\PointInjectionBatches\Support\BulkInjectionUploadSupport;
use App\Jobs\ProcessBulkInjectionJob;
use App\Models\PointInjectionBatch;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use InvalidArgumentException;

class UploadBulkAction
{
    public static function make(): Action
    {
        return Action::make('uploadBulk')
            ->label('Upload Bulk')
            ->icon('heroicon-o-arrow-up-tray')
            ->color('primary')
            ->goldStyle()
            ->modalHeading('Upload Bulk Injeksi Poin')
            ->modalDescription('Unggah file Excel (.xlsx, .xls) atau CSV. Format tanggal transaksi: DD-MM-YYYY (contoh: 31-12-2026). Unduh template untuk contoh kolom.')
            ->modalSubmitActionLabel('Upload & Proses')
            ->form([
                FileUpload::make('spreadsheet')
                    ->label('File Excel')
                    ->disk('content_staging')
                    ->directory('temp')
                    ->acceptedFileTypes([
                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        'application/vnd.ms-excel',
                        'text/csv',
                        'text/plain',
                        'application/csv',
                    ])
                    ->maxSize(5120)
                    ->required()
                    ->maxFiles(1)
                    ->columnSpanFull(),
            ])
            ->action(function (array $data) {
                try {
                    $mediaId = app(BulkInjectionUploadSupport::class)
                        ->storeSpreadsheet($data['spreadsheet'] ?? null);

                    $batch = PointInjectionBatch::query()->create([
                        'staff_id' => Auth::user()?->staff?->id,
                        'media_id' => $mediaId,
                        'resolved' => false,
                        'uploaded_at' => now(),
                    ]);

                    ProcessBulkInjectionJob::dispatch($batch);

                    Notification::make()
                        ->title('Upload berhasil')
                        ->body('File sedang diproses di background. Halaman batch akan menampilkan hasil setelah selesai.')
                        ->success()
                        ->send();

                    return redirect(PointInjectionBatchResource::getUrl('view', ['record' => $batch]));
                } catch (InvalidArgumentException $exception) {
                    Notification::make()
                        ->title('Upload gagal')
                        ->body($exception->getMessage())
                        ->danger()
                        ->send();
                }
            });
    }
}
