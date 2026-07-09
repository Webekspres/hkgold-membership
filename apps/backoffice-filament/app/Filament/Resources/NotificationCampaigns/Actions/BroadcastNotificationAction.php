<?php

declare(strict_types=1);

namespace App\Filament\Resources\NotificationCampaigns\Actions;

use App\Enums\NotificationPlatform;
use App\Filament\Resources\NotificationCampaigns\Support\BroadcastNotificationFormSupport;
use App\Services\Notification\NotificationService;
use Filament\Actions\Action;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Support\Enums\Width;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;

class BroadcastNotificationAction
{
    public static function make(): Action
    {
        return Action::make('broadcastNotification')
            ->label('Kirim Broadcast')
            ->icon('heroicon-o-megaphone')
            ->color('primary')
            ->goldStyle()
            ->modalWidth(Width::TwoExtraLarge)
            ->modalHeading('Kirim Notifikasi Broadcast')
            ->closeModalByClickingAway(false)
            ->modalSubmitActionLabel('Kirim Broadcast')
            ->visible(fn (): bool => Auth::user()?->can('Create:BroadcastNotification') ?? false)
            ->steps([
                Step::make('Konten')
                    ->description('Judul dan isi notifikasi')
                    ->schema([
                        TextInput::make('title')
                            ->label('Judul')
                            ->required()
                            ->maxLength(150)
                            ->columnSpanFull(),
                        Textarea::make('body')
                            ->label('Isi')
                            ->required()
                            ->rows(4)
                            ->columnSpanFull(),
                        Placeholder::make('platform_info')
                            ->label('Platform')
                            ->content('Push Aplikasi Mobile (FCM)'),
                    ]),
                Step::make('Audience')
                    ->description('Tentukan siapa yang ditarget')
                    ->schema([
                        Select::make('audience_type')
                            ->label('Tipe audience')
                            ->options(BroadcastNotificationFormSupport::audienceTypeOptions())
                            ->default('all_active_members')
                            ->required()
                            ->live()
                            ->native(false),
                        Select::make('tier')
                            ->label('Tier member')
                            ->options(BroadcastNotificationFormSupport::tierOptions())
                            ->required()
                            ->visible(fn (Get $get): bool => $get('audience_type') === 'tier')
                            ->native(false),
                        Select::make('batch_id')
                            ->label('Batch injeksi poin')
                            ->options(fn (): array => BroadcastNotificationFormSupport::batchOptions())
                            ->searchable()
                            ->required()
                            ->visible(fn (Get $get): bool => $get('audience_type') === 'batch')
                            ->native(false),
                    ]),
                Step::make('Konfirmasi')
                    ->description('Periksa ringkasan sebelum mengirim')
                    ->schema([
                        Placeholder::make('summary')
                            ->label('Ringkasan broadcast')
                            ->content(function (Get $get): HtmlString {
                                $data = [
                                    'title' => $get('title'),
                                    'body' => $get('body'),
                                    'audience_type' => $get('audience_type'),
                                    'tier' => $get('tier'),
                                    'batch_id' => $get('batch_id'),
                                ];

                                $criteria = BroadcastNotificationFormSupport::buildCriteria($data);
                                $targetedCount = app(NotificationService::class)->resolveTargetedCount($criteria);

                                return new HtmlString(
                                    BroadcastNotificationFormSupport::buildConfirmationHtml($data, $targetedCount),
                                );
                            }),
                    ]),
            ])
            ->action(function (array $data, Action $action): void {
                $criteria = BroadcastNotificationFormSupport::buildCriteria($data);

                app(NotificationService::class)->broadcastMass(
                    title: (string) $data['title'],
                    body: (string) $data['body'],
                    criteria: $criteria,
                    platforms: [NotificationPlatform::MobileAppPush],
                    createdBy: Auth::user(),
                );

                Notification::make()
                    ->title('Kampanye broadcast diantre')
                    ->body('Notifikasi massal akan diproses di background.')
                    ->success()
                    ->send();

                $action->getLivewire()->dispatch('$refresh');
            });
    }
}
