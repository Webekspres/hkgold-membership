<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Filament\Resources\Members\Schemas\MemberInfolist;
use App\Models\Member;
use BackedEnum;
use BezhanSalleh\FilamentShield\Support\Utils;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

/**
 * @property-read Schema $form
 * @property-read Schema $infolist
 */
class MemberLookupPage extends Page
{
    protected static ?string $navigationLabel = 'Cari Member';

    protected static ?string $title = 'Cari Member';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMagnifyingGlass;

    protected static string|UnitEnum|null $navigationGroup = 'Manajemen Pengguna';

    protected static ?int $navigationSort = 1;

    protected static ?string $slug = 'cari-member';

    protected string $view = 'filament.pages.member-lookup-page';

    public bool $searched = false;

    public bool $notFound = false;

    public ?Member $member = null;

    /**
     * @var array<string, mixed>|null
     */
    public ?array $data = [];

    public static function canAccess(): bool
    {
        $user = Auth::user();

        if ($user === null) {
            return false;
        }

        if ($user->hasRole(Utils::getSuperAdminName())) {
            return false;
        }

        return $user->can('View:MemberLookupPage');
    }

    public function mount(): void
    {
        $this->form->fill([
            'member_number' => null,
        ]);
    }

    public function search(): void
    {
        $memberNumber = trim((string) ($this->form->getState()['member_number'] ?? ''));

        $this->searched = true;
        $this->notFound = false;
        $this->member = null;

        if ($memberNumber === '') {
            $this->searched = false;

            return;
        }

        $this->member = Member::query()
            ->with([
                'user.profilePhoto',
                'registeredBranch',
                'address.village.subDistrict.city.province',
                'address.postalCode',
            ])
            ->whereRaw('UPPER(member_number) = ?', [strtoupper($memberNumber)])
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ])
            ->first();

        $this->notFound = $this->member === null;
    }

    public function defaultForm(Schema $schema): Schema
    {
        return $schema
            ->statePath('data');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('member_number')
                    ->label('Kode Member')
                    ->placeholder('Contoh: 2606-0001')
                    ->required()
                    ->maxLength(50)
                    ->columnSpanFull(),
            ]);
    }

    public function defaultInfolist(Schema $schema): Schema
    {
        return $schema
            ->record($this->member)
            ->columns(2);
    }

    public function infolist(Schema $schema): Schema
    {
        return MemberInfolist::configure($schema);
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                $this->getSearchFormComponent(),
            ]);
    }

    public function getSearchFormComponent(): Component
    {
        return Form::make([EmbeddedSchema::make('form')])
            ->id('member-lookup-form')
            ->livewireSubmitHandler('search')
            ->footer([
                Actions::make([
                    Action::make('search')
                        ->label('Cari')
                        ->submit('search')
                        ->keyBindings(['mod+enter']),
                ])
                    ->alignment($this->getFormActionsAlignment())
                    ->fullWidth($this->hasFullWidthFormActions())
                    ->key('member-lookup-form-actions'),
            ]);
    }

    public function getInfolistContentComponent(): Component
    {
        return EmbeddedSchema::make('infolist');
    }

    protected function hasFullWidthFormActions(): bool
    {
        return false;
    }
}
