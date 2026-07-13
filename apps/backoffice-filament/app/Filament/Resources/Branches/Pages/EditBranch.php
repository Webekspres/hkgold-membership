<?php

declare(strict_types=1);

namespace App\Filament\Resources\Branches\Pages;

use App\Enums\ActivityLogAction;
use App\Filament\Resources\Branches\BranchResource;
use App\Filament\Resources\Branches\Support\BranchFormSupport;
use App\Filament\Resources\Members\Support\MemberFormSupport;
use App\Services\ActivityLog\ActivityLogger;
use App\Support\ActivityLog\ActivityLogSanitizer;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EditBranch extends EditRecord
{
    protected static string $resource = BranchResource::class;

    /**
     * @return array<class-string>
     */
    public function getRelationManagers(): array
    {
        return [];
    }

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        $this->record->loadMissing(['normalizedAddress']);

        return [
            ...$data,
            ...BranchFormSupport::addressState($this->record),
            'phone' => MemberFormSupport::formatPhoneForDisplay($this->record->phone),
        ];
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $state = BranchFormSupport::formState($this->form);
        $before = ActivityLogSanitizer::extract($record);

        $updatedRecord = DB::transaction(function () use ($record, $data, $state): Model {
            $addressId = BranchFormSupport::upsertAddress(
                $state,
                $record->normalizedAddress,
            );

            $record->update([
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

            return $record->refresh();
        });

        app(ActivityLogger::class)->log(
            action: ActivityLogAction::BranchUpdated,
            description: 'Memperbarui data cabang',
            auditable: $updatedRecord,
            ipAddress: (string) request()->ip(),
            before: $before,
            after: ActivityLogSanitizer::extract($updatedRecord),
            actor: Auth::user(),
        );

        return $updatedRecord;
    }
}
