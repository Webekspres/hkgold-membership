<?php

declare(strict_types=1);

namespace App\Filament\Resources\PhoneApprovals\Schemas;

use App\Enums\ApprovalStatus;
use App\Enums\ChangePhoneSource;
use App\Filament\Resources\Members\Support\MemberFormSupport;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PhoneApprovalInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Permintaan')
                    ->schema([
                        TextEntry::make('member.user.full_name')
                            ->label('Nama Member')
                            ->placeholder('—'),
                        TextEntry::make('member.member_number')
                            ->label('Nomor Member')
                            ->placeholder('—'),
                        TextEntry::make('old_phone_number')
                            ->label('Nomor Lama')
                            ->formatStateUsing(
                                fn (?string $state): string => $state
                                    ? MemberFormSupport::formatPhoneForDisplay($state)
                                    : '—'
                            ),
                        TextEntry::make('new_phone_number')
                            ->label('Nomor Baru')
                            ->formatStateUsing(
                                fn (?string $state): string => $state
                                    ? MemberFormSupport::formatPhoneForDisplay($state)
                                    : '—'
                            ),
                        TextEntry::make('source')
                            ->label('Sumber')
                            ->formatStateUsing(
                                fn (?ChangePhoneSource $state): string => $state?->label() ?? '—'
                            ),
                        TextEntry::make('status')
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
                        TextEntry::make('reason')
                            ->label('Alasan Member')
                            ->placeholder('—')
                            ->columnSpanFull(),
                        TextEntry::make('action_notes')
                            ->label('Catatan Admin')
                            ->placeholder('—')
                            ->columnSpanFull(),
                        TextEntry::make('created_at')
                            ->label('Diajukan')
                            ->dateTime('d M Y H:i'),
                        TextEntry::make('processed_at')
                            ->label('Diproses')
                            ->dateTime('d M Y H:i')
                            ->placeholder('—'),
                        TextEntry::make('approvedBy.user.full_name')
                            ->label('Diproses Oleh')
                            ->placeholder('—'),
                    ])
                    ->columns(2),
            ]);
    }
}
