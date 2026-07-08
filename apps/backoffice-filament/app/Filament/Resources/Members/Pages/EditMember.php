<?php

declare(strict_types=1);

namespace App\Filament\Resources\Members\Pages;

use App\Enums\ActivityLogAction;
use App\Filament\Resources\Members\MemberResource;
use App\Filament\Resources\Members\Support\MemberFormSupport;
use App\Services\ActivityLog\ActivityLogger;
use App\Support\ActivityLog\ActivityLogSanitizer;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
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
            'address.village.subDistrict.city',
            'address.postalCode',
        ]);

        $address = $this->record->address;
        $village = $address?->village;
        $subDistrict = $village?->subDistrict;
        $city = $subDistrict?->city;

        return [
            ...$data,
            'full_name' => $this->record->user?->full_name,
            'email' => $this->record->user?->email,
            'phone_number' => MemberFormSupport::formatPhoneForDisplay($this->record->phone_number),
            'is_active' => $this->record->user?->is_active ?? true,
            'member_number' => $this->record->member_number,
            'registered_at_branch_id' => $this->record->registered_at_branch_id,
            'current_tier' => $this->record->current_tier,
            'is_suspended' => $this->record->is_suspended,
            'province_id' => $city?->province_id,
            'city_id' => $subDistrict?->city_id,
            'sub_district_id' => $village?->sub_district_id,
            'village_id' => $address?->village_id,
            'postal_code_id' => $address?->postal_code_id,
            'street' => $address?->street,
        ];
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $state = MemberFormSupport::formState($this->form);
        $before = ActivityLogSanitizer::extract($record);

        $updatedRecord = DB::transaction(function () use ($record, $data, $state): Model {
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
                'member_number' => $data['member_number'],
                'phone_number' => MemberFormSupport::normalizePhone($state['phone_number'] ?? null),
                'registered_at_branch_id' => filled($data['registered_at_branch_id'] ?? null)
                    ? $data['registered_at_branch_id']
                    : null,
                'address_id' => $addressId,
                'current_tier' => $data['current_tier'],
                'is_suspended' => $data['is_suspended'] ?? false,
            ]);

            return $record->refresh();
        });

        app(ActivityLogger::class)->log(
            action: ActivityLogAction::MemberUpdated,
            description: 'Memperbarui data anggota',
            auditable: $updatedRecord,
            ipAddress: (string) request()->ip(),
            before: $before,
            after: ActivityLogSanitizer::extract($updatedRecord),
            actor: Auth::user(),
        );

        return $updatedRecord;
    }
}
