<?php

declare(strict_types=1);

namespace App\Filament\Resources\Staff\Tables;

use App\Enums\Role;
use App\Filament\Resources\Staff\Schemas\StaffForm;
use App\Filament\Resources\Staff\Schemas\StaffInfolist;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class StaffTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.full_name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('user.email')
                    ->label('Email')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('employee_code')
                    ->label('Kode karyawan')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->copyMessage('Kode karyawan disalin')
                    ->copyMessageDuration(1500)
                    ->toggleable(),

                TextColumn::make('branch.name')
                    ->label('Cabang')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('user.role')
                    ->label('Role')
                    ->badge()
                    ->formatStateUsing(fn (Role $state): string => StaffInfolist::roleLabel($state))
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label('Terdaftar')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('role')
                    ->label('Role')
                    ->options(StaffForm::roleOptions())
                    ->query(fn (Builder $query, array $data): Builder => $query->when(
                        filled($data['value'] ?? null),
                        fn (Builder $query): Builder => $query->whereHas(
                            'user',
                            fn (Builder $userQuery): Builder => $userQuery->where('role', $data['value']),
                        ),
                    )),
            ])
            ->filtersLayout(FiltersLayout::AboveContentCollapsible)
            ->recordActions([
                ViewAction::make(),
            ]);
    }
}
