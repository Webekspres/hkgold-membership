<?php

declare(strict_types=1);

namespace App\Filament\Resources\Members\Pages;

use App\Enums\Role;
use App\Filament\Resources\Members\MemberResource;
use App\Filament\Resources\Members\Support\MemberFormSupport;
use App\Models\Member;
use App\Models\User;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CreateMember extends CreateRecord
{
    protected static string $resource = MemberResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $state = MemberFormSupport::formState($this->form);

        return DB::transaction(function () use ($data, $state): Member {
            $profilePhotoId = MemberFormSupport::storeProfilePhoto(
                $state['profile_photo'] ?? null,
                (string) $state['name'],
            );

            $user = User::query()->create([
                'name' => $state['name'],
                'email' => $state['email'],
                'phone' => MemberFormSupport::normalizePhone($state['phone'] ?? null),
                'password' => $state['password'],
                'role' => Role::Customer,
                'profile_photo_id' => $profilePhotoId,
                'is_active' => $state['is_active'] ?? true,
            ]);

            $addressId = MemberFormSupport::syncAddress($state);

            return Member::query()->create([
                'id' => $user->id,
                'member_code' => MemberFormSupport::generateMemberCode(),
                'address_id' => $addressId,
                'dob' => $data['dob'] ?? null,
                'total_points' => $data['total_points'] ?? 0,
                'tier' => $data['tier'],
                'phone_change_pending' => $data['phone_change_pending'] ?? false,
            ]);
        });
    }
}
