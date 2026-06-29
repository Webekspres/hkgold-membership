<?php

declare(strict_types=1);

namespace App\Filament\Resources\Branches\Pages;

use App\Filament\Resources\Branches\BranchResource;
use App\Filament\Resources\Branches\Support\BranchFormSupport;
use App\Filament\Resources\Members\Support\MemberFormSupport;
use App\Models\Branch;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CreateBranch extends CreateRecord
{
    protected static string $resource = BranchResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $state = BranchFormSupport::formState($this->form);

        return DB::transaction(function () use ($data, $state): Branch {
            $addressId = BranchFormSupport::upsertAddress($state);

            return Branch::query()->create([
                'branch_code' => $data['branch_code'],
                'name' => $data['name'],
                'phone' => filled($data['phone'] ?? null)
                    ? MemberFormSupport::normalizePhone($data['phone'])
                    : null,
                'is_online_warehouse' => $data['is_online_warehouse'] ?? false,
                'location_url' => $data['location_url'] ?? null,
                'address_id' => $addressId,
                'address' => BranchFormSupport::buildAddressString($state),
            ]);
        });
    }
}
