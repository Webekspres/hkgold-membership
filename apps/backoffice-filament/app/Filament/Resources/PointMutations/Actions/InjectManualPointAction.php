<?php

declare(strict_types=1);

namespace App\Filament\Resources\PointMutations\Actions;

use App\Data\Loyalty\ManualPointInjectionData;
use App\Exceptions\Loyalty\ManualPointInjectionException;
use App\Filament\Resources\PointMutations\Support\ManualPointInjectionFormSupport;
use App\Models\Branch;
use App\Models\TransactionType;
use App\Services\Loyalty\ManualPointInjectionService;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Support\Enums\Width;
use Filament\Support\Exceptions\Halt;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;

class InjectManualPointAction
{
    public static function make(): Action
    {
        return Action::make('injectManualPoint')
            ->label('Tambah Poin')
            ->icon('heroicon-o-plus-circle')
            ->color('primary')
            ->goldStyle()
            ->modalWidth(Width::TwoExtraLarge)
            ->modalHeading('Suntik Poin Manual')
            ->closeModalByClickingAway(false)
            ->modalSubmitActionLabel('Konfirmasi Suntik Poin')
            ->steps([
                Step::make('Data Transaksi')
                    ->description('Isi data transaksi member')
                    ->columns(2)
                    ->schema(self::inputSchema())
                    ->afterValidation(function (Get $get): void {
                        $data = [
                            'member_id' => $get('member_id'),
                            'branch_id' => $get('branch_id'),
                            'transaction_type_id' => $get('transaction_type_id'),
                            'purchase_nominal' => $get('purchase_nominal'),
                            'receipt_number' => $get('receipt_number'),
                            'transaction_date' => $get('transaction_date'),
                        ];

                        try {
                            app(ManualPointInjectionService::class)
                                ->preview(ManualPointInjectionData::fromArray($data));
                        } catch (ManualPointInjectionException $exception) {
                            Notification::make()
                                ->title('Validasi gagal')
                                ->body($exception->getMessage())
                                ->danger()
                                ->send();

                            throw new Halt;
                        }
                    }),
                Step::make('Konfirmasi')
                    ->description('Periksa ringkasan sebelum menyimpan')
                    ->columns(2)
                    ->schema([
                        Placeholder::make('summary')
                            ->label('Ringkasan transaksi')
                            ->content(function (Get $get): HtmlString {
                                $state = [
                                    'member_id' => $get('member_id'),
                                    'branch_id' => $get('branch_id'),
                                    'transaction_type_id' => $get('transaction_type_id'),
                                    'purchase_nominal' => $get('purchase_nominal'),
                                    'receipt_number' => $get('receipt_number'),
                                    'transaction_date' => $get('transaction_date'),
                                ];

                                return new HtmlString(ManualPointInjectionFormSupport::buildPreviewHtml($state));
                            }),
                    ]),
            ])
            ->action(function (array $data, ManualPointInjectionService $service, Action $action): void {
                try {
                    $result = $service->inject(
                        ManualPointInjectionData::fromArray($data),
                        Auth::user(),
                        request()->ip() ?? '127.0.0.1',
                    );
                } catch (ManualPointInjectionException $exception) {
                    Notification::make()
                        ->title('Suntik poin gagal')
                        ->body($exception->getMessage())
                        ->danger()
                        ->send();

                    return;
                }

                $tierMessage = $result->tierUpgraded
                    ? sprintf(' Tier naik menjadi %s.', $result->newTier->value)
                    : '';

                Notification::make()
                    ->title('Suntik poin berhasil')
                    ->body(sprintf(
                        '+%s poin untuk %s. Saldo baru: %s.%s',
                        number_format($result->pointsIssued, 0, ',', '.'),
                        $result->memberName,
                        number_format($result->newBalance, 0, ',', '.'),
                        $tierMessage,
                    ))
                    ->success()
                    ->send();

                $action->getLivewire()->dispatch('$refresh');
            });
    }

    /**
     * @return array<int, Select|TextInput|DatePicker>
     */
    private static function inputSchema(): array
    {
        return [
            Select::make('member_id')
                ->label('Nomor member')
                ->searchable()
                ->searchPrompt('Ketik minimal 2 karakter nama atau nomor member')
                ->searchDebounce(400)
                ->required()
                ->getSearchResultsUsing(function (string $search): array {
                    if (mb_strlen(trim($search)) < 2) {
                        return [];
                    }

                    return ManualPointInjectionFormSupport::searchMembers($search);
                })
                ->getOptionLabelUsing(fn (?string $value): ?string => ManualPointInjectionFormSupport::memberOptionLabel($value)),

            Select::make('branch_id')
                ->label('Cabang')
                ->searchable()
                ->preload()
                ->options(fn (): array => Branch::query()->orderBy('name')->pluck('name', 'id')->all()),

            Select::make('transaction_type_id')
                ->label('Jenis transaksi')
                ->searchable()
                ->preload()
                ->required()
                ->options(fn (): array => TransactionType::query()->orderBy('display_name')->pluck('display_name', 'id')->all()),

            TextInput::make('purchase_nominal')
                ->label('Nominal belanja')
                ->prefix('Rp')
                ->numeric()
                ->minValue(1)
                ->required(),

            TextInput::make('receipt_number')
                ->label('Nomor struk')
                ->maxLength(100),

            DatePicker::make('transaction_date')
                ->label('Tanggal transaksi')
                ->required()
                ->default(now())
                ->maxDate(now()),
        ];
    }
}
