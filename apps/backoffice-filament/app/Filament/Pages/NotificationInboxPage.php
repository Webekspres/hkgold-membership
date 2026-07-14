<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Enums\NotificationPlatform;
use App\Filament\Pages\Actions\MarkAllNotificationsAsReadAction;
use App\Models\Notification;
use App\Services\Notification\NotificationDispatcher;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Notifications\Notification as FilamentNotification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class NotificationInboxPage extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationLabel = 'Kotak Masuk';

    protected static ?string $title = 'Kotak Masuk';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBell;

    protected static string|UnitEnum|null $navigationGroup = 'Notifikasi';

    protected static ?int $navigationSort = 1;

    protected static ?string $slug = 'inbox-notifikasi';

    protected string $view = 'filament.pages.notification-inbox-page';

    public static function canAccess(): bool
    {
        return Auth::check();
    }

    public static function getNavigationBadge(): ?string
    {
        $user = Auth::user();

        if ($user === null) {
            return null;
        }

        $count = app(NotificationDispatcher::class)->unreadCountForUser($user);

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }

    protected function getHeaderActions(): array
    {
        return [
            MarkAllNotificationsAsReadAction::make(),
        ];
    }

    public function table(Table $table): Table
    {
        $userId = Auth::id();

        return $table
            ->query(
                Notification::query()
                    ->where('user_id', $userId)
                    ->where('platform', NotificationPlatform::WebAdminInApp)
                    ->latest(),
            )
            ->heading('Notifikasi Admin')
            ->emptyStateHeading('Belum ada notifikasi')
            ->emptyStateDescription('Notifikasi operasional akan muncul di sini.')
            ->columns([
                TextColumn::make('title')
                    ->label('Judul')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('body')
                    ->label('Isi')
                    ->limit(80)
                    ->wrap(),

                TextColumn::make('read_at')
                    ->label('Status Baca')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => $state === null ? 'Belum Dibaca' : 'Dibaca')
                    ->color(fn (?string $state): string => $state === null ? 'warning' : 'success'),

                TextColumn::make('created_at')
                    ->label('Waktu')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->filters([
                TernaryFilter::make('read_at')
                    ->label('Status Baca')
                    ->nullable()
                    ->placeholder('Semua')
                    ->trueLabel('Dibaca')
                    ->falseLabel('Belum Dibaca')
                    ->queries(
                        true: fn (Builder $query): Builder => $query->whereNotNull('read_at'),
                        false: fn (Builder $query): Builder => $query->whereNull('read_at'),
                    ),
            ])
            ->recordActions([
                Action::make('tandaiDibaca')
                    ->label('Tandai Dibaca')
                    ->icon('heroicon-o-check')
                    ->color('gray')
                    ->visible(fn (Notification $record): bool => $record->read_at === null)
                    ->action(function (Notification $record): void {
                        app(NotificationDispatcher::class)->markAsRead($record);

                        FilamentNotification::make()
                            ->title('Notifikasi ditandai dibaca')
                            ->success()
                            ->send();
                    }),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
