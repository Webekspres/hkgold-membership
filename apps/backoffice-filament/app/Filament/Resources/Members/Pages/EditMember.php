<?php

declare(strict_types=1);

namespace App\Filament\Resources\Members\Pages;

use App\Filament\Resources\Members\MemberResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class EditMember extends EditRecord
{
    protected static string $resource = MemberResource::class;

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
        $this->record->loadMissing('user');

        return [
            ...$data,
            'name' => $this->record->user?->name,
            'email' => $this->record->user?->email,
            'phone' => $this->record->user?->phone,
            'profile_photo_id' => $this->record->user?->profile_photo_id,
            'is_active' => $this->record->user?->is_active ?? true,
        ];
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $state = $this->form->getState();

        return DB::transaction(function () use ($record, $data, $state): Model {
            $userData = [
                'name' => $state['name'],
                'email' => $state['email'],
                'phone' => $state['phone'],
                'profile_photo_id' => $state['profile_photo_id'] ?? null,
                'is_active' => $state['is_active'] ?? true,
            ];

            if (filled($state['password'] ?? null)) {
                $userData['password'] = $state['password'];
            }

            $record->user()->update($userData);

            $record->update([
                'member_code' => $data['member_code'],
                'address_id' => $data['address_id'] ?? null,
                'dob' => $data['dob'] ?? null,
                'total_points' => $data['total_points'] ?? 0,
                'tier' => $data['tier'],
                'phone_change_pending' => $data['phone_change_pending'] ?? false,
            ]);

            return $record->refresh();
        });
    }
}
