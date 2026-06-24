<?php

declare(strict_types=1);

namespace App\Filament\Resources\Members\Pages;

use App\Filament\Resources\Members\MemberResource;
use App\Filament\Resources\Members\Support\MemberFormSupport;
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
        $this->record->loadMissing([
            'user',
            'address.village.district.regency',
            'address.postalCode',
        ]);

        $address = $this->record->address;
        $village = $address?->village;
        $district = $village?->district;
        $regency = $district?->regency;

        return [
            ...$data,
            'full_name' => $this->record->user?->full_name,
            'email' => $this->record->user?->email,
            'phone_number' => MemberFormSupport::formatPhoneForDisplay($this->record->phone_number),
            'is_active' => $this->record->user?->is_active ?? true,
            'registered_at_branch_id' => $this->record->registered_at_branch_id,
            'current_tier' => $this->record->current_tier,
            'point_balance' => $this->record->point_balance,
            'is_suspended' => $this->record->is_suspended,
            'province_id' => $regency?->province_id,
            'regency_id' => $district?->regency_id,
            'district_id' => $village?->district_id,
            'village_id' => $address?->village_id,
            'postal_code_id' => $address?->postal_code_id,
            'street' => $address?->street,
        ];
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $state = MemberFormSupport::formState($this->form);

        return DB::transaction(function () use ($record, $data, $state): Model {
            $profilePhotoId = MemberFormSupport::storeProfilePhoto(
                $state['profile_photo'] ?? null,
                (string) $state['full_name'],
            ) ?? $record->user?->profile_photo_id;

            $userData = [
                'full_name' => $state['full_name'],
                'email' => $state['email'],
                'profile_photo_id' => $profilePhotoId,
                'is_active' => $state['is_active'] ?? true,
            ];

            if (filled($state['password'] ?? null)) {
                $userData['password'] = $state['password'];
            }

            $record->user()->update($userData);

            $addressId = MemberFormSupport::syncAddress($state, $record->address);

            $record->update([
                'phone_number' => MemberFormSupport::normalizePhone($state['phone_number'] ?? null),
                'registered_at_branch_id' => $data['registered_at_branch_id'],
                'address_id' => $addressId,
                'current_tier' => $data['current_tier'],
                'point_balance' => (int) ($data['point_balance'] ?? 0),
                'is_suspended' => $data['is_suspended'] ?? false,
            ]);

            return $record->refresh();
        });
    }
}
