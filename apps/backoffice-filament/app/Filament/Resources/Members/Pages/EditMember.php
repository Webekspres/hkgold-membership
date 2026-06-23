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
            'name' => $this->record->user?->name,
            'email' => $this->record->user?->email,
            'phone' => MemberFormSupport::formatPhoneForDisplay($this->record->user?->phone),
            'is_active' => $this->record->user?->is_active ?? true,
            'province_id' => $regency?->province_id,
            'city_id' => $district?->city_id,
            'sub_district_id' => $village?->sub_district_id,
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
                (string) $state['name'],
            ) ?? $record->user?->profile_photo_id;

            $userData = [
                'name' => $state['name'],
                'email' => $state['email'],
                'phone' => MemberFormSupport::normalizePhone($state['phone'] ?? null),
                'profile_photo_id' => $profilePhotoId,
                'is_active' => $state['is_active'] ?? true,
            ];

            if (filled($state['password'] ?? null)) {
                $userData['password'] = $state['password'];
            }

            $record->user()->update($userData);

            $addressId = MemberFormSupport::syncAddress($state, $record->address);

            $record->update([
                'address_id' => $addressId,
                'dob' => $data['dob'] ?? null,
                'total_points' => $data['total_points'] ?? 0,
                'tier' => $data['tier'],
                'phone_change_pending' => $data['phone_change_pending'] ?? false,
            ]);

            return $record->refresh();
        });
    }
}
