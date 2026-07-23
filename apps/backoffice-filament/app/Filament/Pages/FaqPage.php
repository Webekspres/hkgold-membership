<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Enums\ActivityLogAction;
use App\Filament\Pages\Support\FaqSupport;
use App\Models\FaqItem;
use App\Services\ActivityLog\ActivityLogger;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Concerns\CanUseDatabaseTransactions;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Schema;
use Filament\Support\Exceptions\Halt;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;
use Throwable;
use UnitEnum;

/**
 * @property-read Schema $form
 */
class FaqPage extends Page
{
    use CanUseDatabaseTransactions;

    protected static ?string $navigationLabel = 'FAQ';

    protected static ?string $title = 'FAQ';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedQuestionMarkCircle;

    protected static string|UnitEnum|null $navigationGroup = 'CMS';

    protected static ?int $navigationSort = 3;

    protected static ?string $slug = 'faq';

    protected string $view = 'filament.pages.faq-page';

    public static function canAccess(): bool
    {
        $user = Auth::user();

        return $user !== null && $user->can('View:FaqPage');
    }

    protected function canManageFaq(): bool
    {
        $user = Auth::user();

        return $user !== null && $user->can('Update:FaqPage');
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
            'items' => FaqItem::query()
                ->orderBy('sort_order')
                ->get()
                ->map(fn (FaqItem $item): array => [
                    'id' => $item->id,
                    'question' => $item->question,
                    'answer' => $item->answer,
                ])
                ->all(),
        ]);
    }

    public function save(): void
    {
        abort_unless($this->canManageFaq(), 403);

        try {
            $this->beginDatabaseTransaction();

            $data = $this->form->getState();

            FaqSupport::syncItems($data['items'] ?? []);

            $this->commitDatabaseTransaction();

            app(ActivityLogger::class)->logWithKey(
                action: ActivityLogAction::FaqUpdated,
                description: 'Memperbarui FAQ',
                auditableType: 'FaqPage',
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
            ->title('FAQ berhasil disimpan')
            ->send();
    }

    public function resetForm(): void
    {
        abort_unless($this->canManageFaq(), 403);

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
        $canManage = $this->canManageFaq();

        return $schema
            ->components([
                Repeater::make('items')
                    ->label('Daftar FAQ')
                    ->disabled(! $canManage)
                    ->deletable($canManage)
                    ->reorderable($canManage)
                    ->schema([
                        Hidden::make('id'),
                        TextInput::make('question')
                            ->label('Pertanyaan')
                            ->required()
                            ->trim()
                            ->maxLength(255)
                            ->disabled(! $canManage)
                            ->columnSpanFull(),
                        Textarea::make('answer')
                            ->label('Jawaban')
                            ->required()
                            ->trim()
                            ->rows(3)
                            ->disabled(! $canManage)
                            ->columnSpanFull(),
                    ])
                    ->collapsible()
                    ->itemLabel(fn (array $state): ?string => $state['question'] ?? null)
                    ->columnSpanFull()
                    ->addActionLabel('Tambah FAQ')
                    ->addable($canManage),
            ]);
    }

    /**
     * @return array<Action>
     */
    protected function getHeaderActions(): array
    {
        if (! $this->canManageFaq()) {
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

        if ($this->canManageFaq()) {
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
