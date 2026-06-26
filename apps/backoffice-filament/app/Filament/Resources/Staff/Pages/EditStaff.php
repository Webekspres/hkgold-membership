<?php

declare(strict_types=1);

namespace App\Filament\Resources\Staff\Pages;

use App\Enums\Role;
use App\Filament\Resources\Staff\StaffResource;
use App\Filament\Resources\Staff\Support\StaffFormSupport;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class EditStaff extends EditRecord
{
    protected static string $resource = StaffResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        $this->record->loadMissing(['user']);

        return [
            ...$data,
            'full_name' => $this->record->user?->full_name,
            'email' => $this->record->user?->email,
            'role' => $this->record->user?->role?->value,
            'is_active' => $this->record->user?->is_active ?? true,
            'branch_id' => $this->record->branch_id,
            'employee_code' => $this->record->employee_code,
        ];
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $state = StaffFormSupport::formState($this->form);

        return DB::transaction(function () use ($record, $data, $state): Model {
            $profilePhotoId = StaffFormSupport::storeProfilePhoto(
                $state['profile_photo'] ?? null,
                (string) $state['full_name'],
            ) ?? $record->user?->profile_photo_id;

            $userData = [
                'full_name' => $state['full_name'],
                'email' => $state['email'],
                'role' => Role::from((string) $state['role']),
                'profile_photo_id' => $profilePhotoId,
                'is_active' => $state['is_active'] ?? true,
            ];

            if (filled($state['password'] ?? null)) {
                $userData['password'] = $state['password'];
            }

            $record->user()->update($userData);

            $record->update([
                'branch_id' => $data['branch_id'],
                'employee_code' => $data['employee_code'],
            ]);

            return $record->refresh();
        });
    }
}
