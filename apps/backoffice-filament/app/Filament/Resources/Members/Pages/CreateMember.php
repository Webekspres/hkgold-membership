<?php

declare(strict_types=1);

namespace App\Filament\Resources\Members\Pages;

use App\Enums\ActivityLogAction;
use App\Enums\Role;
use App\Filament\Resources\Members\MemberResource;
use App\Filament\Resources\Members\Support\MemberFormSupport;
use App\Models\Member;
use App\Models\User;
use App\Services\ActivityLog\ActivityLogger;
use App\Support\ActivityLog\ActivityLogSanitizer;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CreateMember extends CreateRecord
{
    protected static string $resource = MemberResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $state = MemberFormSupport::formState($this->form);

        $record = DB::transaction(function () use ($data, $state): Member {
            $profilePhotoId = MemberFormSupport::storeProfilePhoto(
                $state['profile_photo'] ?? null,
                (string) $state['full_name'],
            );

            $user = User::query()->create([
                'full_name' => $state['full_name'],
                'email' => $state['email'],
                'password' => $state['password'],
                'role' => Role::Member,
                'profile_photo_id' => $profilePhotoId,
                'is_active' => $state['is_active'] ?? true,
            ]);

            $addressId = MemberFormSupport::syncAddress($state);

            return Member::query()->create([
                'user_id' => $user->id,
                'member_number' => $data['member_number'],
                'phone_number' => MemberFormSupport::normalizePhone($state['phone_number'] ?? null),
                'registered_at_branch_id' => filled($data['registered_at_branch_id'] ?? null)
                    ? $data['registered_at_branch_id']
                    : null,
                'address_id' => $addressId,
                'current_tier' => $data['current_tier'],
                'point_balance' => 0,
                'highest_point' => 0,
                'is_suspended' => $data['is_suspended'] ?? false,
            ]);
        });

        app(ActivityLogger::class)->log(
            action: ActivityLogAction::MemberCreated,
            description: 'Membuat data anggota baru',
            auditable: $record,
            ipAddress: (string) request()->ip(),
            after: ActivityLogSanitizer::extract($record),
            actor: Auth::user(),
        );

        return $record;
    }
}
