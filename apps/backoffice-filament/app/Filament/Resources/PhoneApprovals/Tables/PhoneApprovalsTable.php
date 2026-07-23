<?php

declare(strict_types=1);

namespace App\Filament\Resources\PhoneApprovals\Tables;

use App\Enums\ApprovalStatus;
use App\Enums\ChangePhoneSource;
use App\Filament\Resources\Members\Support\MemberFormSupport;
use App\Models\PhoneApproval;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PhoneApprovalsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->label('Diajukan')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
                TextColumn::make('member.user.full_name')
                    ->label('Member')
                    ->searchable()
                    ->placeholder('—'),
                TextColumn::make('member.member_number')
                    ->label('No. Member')
                    ->searchable()
                    ->placeholder('—'),
                TextColumn::make('old_phone_number')
                    ->label('Nomor Lama')
                    ->formatStateUsing(
                        fn (?string $state): string => $state
                            ? MemberFormSupport::formatPhoneForDisplay($state)
                            : '—'
                    ),
                TextColumn::make('new_phone_number')
                    ->label('Nomor Baru')
                    ->formatStateUsing(
                        fn (?string $state): string => $state
                            ? MemberFormSupport::formatPhoneForDisplay($state)
                            : '—'
                    ),
                TextColumn::make('source')
                    ->label('Sumber')
                    ->formatStateUsing(
                        fn (?ChangePhoneSource $state): string => $state?->label() ?? '—'
                    )
                    ->toggleable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(
                        fn (?ApprovalStatus $state): string => match ($state) {
                            ApprovalStatus::Pending => 'Menunggu',
                            ApprovalStatus::Approved => 'Disetujui',
                            ApprovalStatus::Rejected => 'Ditolak',
                            ApprovalStatus::Cancelled => 'Dibatalkan',
                            default => '—',
                        }
                    )
                    ->color(fn (?ApprovalStatus $state): string => match ($state) {
                        ApprovalStatus::Pending => 'warning',
                        ApprovalStatus::Approved => 'success',
                        ApprovalStatus::Rejected => 'danger',
                        ApprovalStatus::Cancelled => 'gray',
                        default => 'gray',
                    }),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        ApprovalStatus::Pending->value => 'Menunggu',
                        ApprovalStatus::Approved->value => 'Disetujui',
                        ApprovalStatus::Rejected->value => 'Ditolak',
                        ApprovalStatus::Cancelled->value => 'Dibatalkan',
                    ])
                    ->default(ApprovalStatus::Pending->value),
            ])
            ->recordActions([
                ViewAction::make()->label('Lihat'),
            ])
            ->recordUrl(fn (PhoneApproval $record): string => PhoneApprovalsTable::viewUrl($record));
    }

    private static function viewUrl(PhoneApproval $record): string
    {
        return \App\Filament\Resources\PhoneApprovals\PhoneApprovalResource::getUrl('view', [
            'record' => $record,
        ]);
    }
}
