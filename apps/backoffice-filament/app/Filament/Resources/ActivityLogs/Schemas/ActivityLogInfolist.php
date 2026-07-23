<?php

declare(strict_types=1);

namespace App\Filament\Resources\ActivityLogs\Schemas;

use App\Models\ActivityLog;
use App\Support\ActivityLog\ActivityLogQuery;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ActivityLogInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Metadata Log')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('created_at')
                            ->label('Waktu')
                            ->dateTime('d M Y, H:i:s'),
                        TextEntry::make('actor')
                            ->label('Pengguna')
                            ->state(fn (ActivityLog $record): string => ActivityLogQuery::actorLabel($record)),
                        TextEntry::make('action')
                            ->label('Aksi')
                            ->state(fn (ActivityLog $record): string => ActivityLogQuery::displayAction($record))
                            ->badge()
                            ->color('gray'),
                        TextEntry::make('auditable_type')
                            ->label('Tipe Entitas'),
                        TextEntry::make('auditable_id')
                            ->label('ID Entitas')
                            ->copyable(),
                        TextEntry::make('ip_address')
                            ->label('Alamat IP'),
                        TextEntry::make('description')
                            ->label('Deskripsi')
                            ->columnSpanFull(),
                    ]),
                Section::make('State Sebelum')
                    ->visible(fn (ActivityLog $record): bool => filled($record->before_json))
                    ->schema([
                        TextEntry::make('before_json')
                            ->label('')
                            ->state(fn (ActivityLog $record): string => self::markdownKeyValue(
                                ActivityLogQuery::formatJsonState(self::normalizeJsonState($record->before_json)),
                            ))
                            ->markdown(),
                    ]),
                Section::make('State Sesudah')
                    ->visible(fn (ActivityLog $record): bool => filled($record->after_json))
                    ->schema([
                        TextEntry::make('after_json')
                            ->label('')
                            ->state(fn (ActivityLog $record): string => self::markdownKeyValue(
                                ActivityLogQuery::formatJsonState(self::normalizeJsonState($record->after_json)),
                            ))
                            ->markdown(),
                    ]),
            ]);
    }

    /**
     * @return array<string, mixed>|null
     */
    private static function normalizeJsonState(mixed $state): ?array
    {
        if (is_array($state)) {
            return $state;
        }

        if (! is_string($state) || blank($state)) {
            return null;
        }

        $decoded = json_decode($state, true);

        return is_array($decoded) ? $decoded : null;
    }

    private static function markdownKeyValue(string $state): string
    {
        if ($state === '—') {
            return $state;
        }

        return collect(explode("\n", $state))
            ->filter()
            ->map(function (string $line): string {
                $parts = explode(': ', $line, 2);

                if (count($parts) === 2) {
                    return "- **{$parts[0]}:** {$parts[1]}";
                }

                return "- {$line}";
            })
            ->implode("\n");
    }
}
