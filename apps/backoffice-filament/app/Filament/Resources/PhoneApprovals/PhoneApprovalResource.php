<?php

declare(strict_types=1);

namespace App\Filament\Resources\PhoneApprovals;

use App\Enums\Role;
use App\Filament\Resources\PhoneApprovals\Pages\ListPhoneApprovals;
use App\Filament\Resources\PhoneApprovals\Pages\ViewPhoneApproval;
use App\Filament\Resources\PhoneApprovals\Schemas\PhoneApprovalInfolist;
use App\Filament\Resources\PhoneApprovals\Tables\PhoneApprovalsTable;
use App\Models\PhoneApproval;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class PhoneApprovalResource extends Resource
{
    protected static ?string $model = PhoneApproval::class;

    protected static ?string $navigationLabel = 'Persetujuan Ganti Nomor';

    protected static ?string $modelLabel = 'Persetujuan Ganti Nomor';

    protected static ?string $pluralModelLabel = 'Persetujuan Ganti Nomor';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDevicePhoneMobile;

    protected static string|\UnitEnum|null $navigationGroup = 'Manajemen Pengguna';

    protected static ?int $navigationSort = 4;

    public static function canAccess(): bool
    {
        $user = Auth::user();

        return $user !== null
            && in_array($user->role, [Role::Administrator, Role::SuperAdmin], true);
    }

    public static function infolist(Schema $schema): Schema
    {
        return PhoneApprovalInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PhoneApprovalsTable::configure($table);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPhoneApprovals::route('/'),
            'view' => ViewPhoneApproval::route('/{record}'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['member.user', 'approvedBy.user', 'requestedBy.user'])
            ->orderByDesc('created_at');
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->with(['member.user', 'approvedBy.user', 'requestedBy.user']);
    }
}
