<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Enums\ActivityLogAction;
use App\Filament\Pages\Support\PromotionBannerSupport;
use App\Filament\Resources\Contents\Support\ContentFormSupport;
use App\Models\PromotionBanner;
use App\Services\ActivityLog\ActivityLogger;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Concerns\CanUseDatabaseTransactions;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Support\Exceptions\Halt;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;
use Throwable;
use UnitEnum;

/**
 * @property-read Schema $form
 */
class PromotionBannerPage extends Page
{
    use CanUseDatabaseTransactions;

    protected static ?string $navigationLabel = 'Banner Promosi';

    protected static ?string $title = 'Banner Promosi';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPhoto;

    protected static string|UnitEnum|null $navigationGroup = 'CMS';

    protected static ?int $navigationSort = 2;

    protected static ?string $slug = 'banner-promosi';

    protected string $view = 'filament.pages.promotion-banner-page';

    public static function canAccess(): bool
    {
        $user = Auth::user();

        return $user !== null && $user->can('View:PromotionBannerPage');
    }

    protected function canManageBanners(): bool
    {
        $user = Auth::user();

        return $user !== null && $user->can('Update:PromotionBannerPage');
    }

    /**
     * @var array<string, mixed>|null
     */
    public ?array $data = [];

    public function mount(): void
    {
        $this->fillForm();
    }

    protected function fillForm(): void
    {
        $this->form->fill([
            'banners' => PromotionBanner::query()
                ->with('media')
                ->orderBy('sort_order')
                ->get()
                ->map(fn (PromotionBanner $banner): array => [
                    'id' => $banner->id,
                    'name' => $banner->name,
                    'is_active' => $banner->is_active,
                    'image' => $banner->media !== null
                        ? ContentFormSupport::mediaToUploadPath($banner->media)
                        : null,
                ])
                ->all(),
        ]);
    }

    public function save(): void
    {
        abort_unless($this->canManageBanners(), 403);

        try {
            $this->beginDatabaseTransaction();

            $data = $this->form->getState();

            PromotionBannerSupport::syncBanners($data['banners'] ?? []);

            $this->commitDatabaseTransaction();

            app(ActivityLogger::class)->logWithKey(
                action: ActivityLogAction::PromotionBannerUpdated,
                description: 'Memperbarui konfigurasi banner promosi',
                auditableType: 'PromotionBannerPage',
                auditableId: 'global',
                ipAddress: (string) request()->ip(),
                actor: Auth::user(),
            );
        } catch (Halt $exception) {
            $exception->shouldRollbackDatabaseTransaction() ?
                $this->rollBackDatabaseTransaction() :
                $this->commitDatabaseTransaction();

            return;
        } catch (Throwable $exception) {
            $this->rollBackDatabaseTransaction();

            throw $exception;
        }

        $this->fillForm();

        Notification::make()
            ->success()
            ->title('Banner promosi berhasil disimpan')
            ->send();
    }

    public function resetForm(): void
    {
        abort_unless($this->canManageBanners(), 403);

        $this->fillForm();

        Notification::make()
            ->info()
            ->title('Perubahan dibatalkan')
            ->send();
    }

    public function defaultForm(Schema $schema): Schema
    {
        return $schema
            ->statePath('data');
    }

    public function form(Schema $schema): Schema
    {
        $canManage = $this->canManageBanners();

        return $schema
            ->components([
                Repeater::make('banners')
                    ->label('Daftar Banner')
                    ->disabled(! $canManage)
                    ->deletable($canManage)
                    ->reorderable($canManage)
                    ->schema([
                        Hidden::make('id'),
                        Grid::make(4)
                            ->schema([
                                TextInput::make('name')
                                    ->label('Nama')
                                    ->required()
                                    ->maxLength(150)
                                    ->disabled(! $canManage)
                                    ->columnSpan(3),
                                Toggle::make('is_active')
                                    ->label('Aktif')
                                    ->default(true)
                                    ->inline(false)
                                    ->disabled(! $canManage)
                                    ->columnSpan(1),
                            ]),
                        FileUpload::make('image')
                            ->label('Gambar Banner')
                            ->image()
                            ->imageEditor()
                            ->imageEditorAspectRatios(['21:9'])
                            ->imageAspectRatio('21:9')
                            ->automaticallyCropImagesToAspectRatio()
                            ->automaticallyResizeImagesToWidth('1200')
                            ->automaticallyResizeImagesToHeight('514')
                            ->automaticallyUpscaleImagesWhenResizing(false)
                            ->disk('r2')
                            ->directory('temp')
                            ->maxSize(512)
                            ->required()
                            ->disabled(! $canManage)
                            ->columnSpanFull(),
                    ])
                    ->collapsible()
                    ->itemLabel(fn (array $state): ?string => $state['name'] ?? null)
                    ->columnSpanFull()
                    ->addActionLabel('Tambah Banner')
                    ->addable($canManage),
            ]);
    }

    /**
     * @return array<Action>
     */
    protected function getHeaderActions(): array
    {
        if (! $this->canManageBanners()) {
            return [];
        }

        return [
            Action::make('reset')
                ->label('Reset')
                ->color('gray')
                ->action('resetForm'),
            Action::make('save')
                ->label('Simpan')
                ->action('save')
                ->keyBindings(['mod+s']),
        ];
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                $this->getFormContentComponent(),
            ]);
    }

    public function getFormContentComponent(): Component
    {
        $formActions = [];

        if ($this->canManageBanners()) {
            $formActions[] = Action::make('save')
                ->label('Simpan')
                ->submit('save')
                ->keyBindings(['mod+s']);
        }

        return Form::make([EmbeddedSchema::make('form')])
            ->id('form')
            ->livewireSubmitHandler('save')
            ->footer([
                Actions::make($formActions)
                    ->alignment($this->getFormActionsAlignment())
                    ->fullWidth($this->hasFullWidthFormActions())
                    ->sticky($this->areFormActionsSticky())
                    ->key('form-actions'),
            ]);
    }

    protected function hasFullWidthFormActions(): bool
    {
        return false;
    }
}
