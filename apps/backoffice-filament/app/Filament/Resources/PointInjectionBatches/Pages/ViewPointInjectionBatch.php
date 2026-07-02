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
                ->with(['transactionType', 'member.user']))
            ->heading('Daftar Baris Injeksi')
            ->description('Detail setiap baris dalam batch upload ini.')
            ->headerActions([
                Action::make('process')
                    ->label('Process')
                    ->button()
                    ->color('primary')
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
