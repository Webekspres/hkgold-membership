<?php

declare(strict_types=1);

namespace App\Filament\Resources\PointAnnualArchives\Pages;

use App\Enums\TierStatus;
use App\Filament\Resources\PointAnnualArchives\PointAnnualArchiveResource;
use App\Models\PointAnnualArchive;
use App\Models\PointAnnualArchivePeriod;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ViewPointAnnualArchive extends ViewRecord implements HasTable
{
    use InteractsWithTable;

    protected static string $resource = PointAnnualArchiveResource::class;

    public function getView(): string
    {
        return 'filament.resources.point-annual-archives.view-point-annual-archive';
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('back')
                ->label('Kembali ke List')
                ->icon('heroicon-o-arrow-left')
                ->color('gray')
                ->url(PointAnnualArchiveResource::getUrl('index')),
        ];
    }

    public function table(Table $table): Table
    {
        /** @var PointAnnualArchivePeriod $record */
        $record = $this->record;

        return $table
            ->query(fn () => PointAnnualArchive::query()
                ->where('period_id', $record->id)
                ->with(['member.user']))
            ->heading('Detail Anggota Terarsip')
            ->columns([
                TextColumn::make('member.user.full_name')
                    ->label('Nama Member')
                    ->searchable()
                    ->sortable()
                    ->placeholder('—'),

                TextColumn::make('member.member_number')
                    ->label('Kode Member')
                    ->searchable()
                    ->sortable()
                    ->placeholder('—'),

                TextColumn::make('frozen_points_total')
                    ->label('Poin Dibekukan')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('highest_point')
                    ->label('Poin Tertinggi')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('last_tier_position')
                    ->label('Tier Terakhir')
                    ->badge()
                    ->formatStateUsing(fn (TierStatus $state): string => match ($state) {
                        TierStatus::Silver => 'Silver',
                        TierStatus::Gold => 'Gold',
                        TierStatus::Platinum => 'Platinum',
                        TierStatus::Sapphire => 'Sapphire',
                    })
                    ->sortable(),

                TextColumn::make('frozen_at')
                    ->label('Waktu Pembekuan')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('frozen_at', 'desc')
            ->filters([
                SelectFilter::make('last_tier_position')
                    ->label('Tier Terakhir')
                    ->options([
                        TierStatus::Silver->value => 'Silver',
                        TierStatus::Gold->value => 'Gold',
                        TierStatus::Platinum->value => 'Platinum',
                        TierStatus::Sapphire->value => 'Sapphire',
                    ]),
            ])
            ->actions([
                Action::make('view_simulated')
                    ->label('Lihat')
                    ->icon('heroicon-o-eye')
                    ->action(function ($record): void {
                        Notification::make()
                            ->title('Detail Anggota (Simulasi)')
                            ->body('Membuka data anggota: '.($record->member?->user?->full_name ?? '—'))
                            ->success()
                            ->send();
                    }),
            ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function getStats(): array
    {
        /** @var PointAnnualArchivePeriod $record */
        $record = $this->record;

        return [
            'name' => $record->name,
            'archive_year' => $record->archive_year,
            'archived_at' => $record->archived_at?->translatedFormat('d M Y, H:i') ?? 'Belum diarsipkan',
            'total_members' => number_format($record->total_members, 0, ',', '.'),
            'frozen_points_total' => number_format($record->frozen_points_total, 0, ',', '.'),
            'redeemed_points_total' => number_format($record->redeemed_points_total, 0, ',', '.'),
            'members_growth' => $record->getMembersGrowthPercent(),
            'frozen_growth' => $record->getFrozenPointsGrowthPercent(),
            'redeemed_growth' => $record->getRedeemedPointsGrowthPercent(),
        ];
    }
}
