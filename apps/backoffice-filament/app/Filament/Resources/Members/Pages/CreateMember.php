<?php

declare(strict_types=1);

namespace App\Filament\Resources\Members\Pages;

use App\Enums\Role;
use App\Filament\Resources\Members\MemberResource;
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
        $state = $this->form->getState();

        return DB::transaction(function () use ($data, $state): Member {
            $user = User::query()->create([
                'name' => $state['name'],
                'email' => $state['email'],
                'phone' => $state['phone'],
                'password' => $state['password'],
                'role' => Role::Customer,
                'profile_photo_id' => $state['profile_photo_id'] ?? null,
                'is_active' => $state['is_active'] ?? true,
            ]);

            return Member::query()->create([
                'id' => $user->id,
                'member_code' => $data['member_code'],
                'address_id' => $data['address_id'] ?? null,
                'dob' => $data['dob'] ?? null,
                'total_points' => $data['total_points'] ?? 0,
                'tier' => $data['tier'],
                'phone_change_pending' => $data['phone_change_pending'] ?? false,
            ]);
        });
    }
}
