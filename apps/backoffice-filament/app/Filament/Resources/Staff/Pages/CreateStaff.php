<?php

declare(strict_types=1);

namespace App\Filament\Resources\Staff\Pages;

use App\Enums\Role;
use App\Filament\Resources\Staff\StaffResource;
use App\Filament\Resources\Staff\Support\StaffFormSupport;
use App\Models\Staff;
use App\Models\User;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CreateStaff extends CreateRecord
{
    protected static string $resource = StaffResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $state = StaffFormSupport::formState($this->form);

        return DB::transaction(function () use ($data, $state): Staff {
            $profilePhotoId = StaffFormSupport::storeProfilePhoto(
                $state['profile_photo'] ?? null,
                (string) $state['full_name'],
            );

            $user = User::query()->create([
                'full_name' => $state['full_name'],
                'email' => $state['email'],
                'password' => $state['password'],
                'role' => Role::from((string) $state['role']),
                'profile_photo_id' => $profilePhotoId,
                'is_active' => $state['is_active'] ?? true,
            ]);

            return Staff::query()->create([
                'user_id' => $user->id,
                'branch_id' => $data['branch_id'],
                'employee_code' => $data['employee_code'],
            ]);
        });
    }
}
