<?php

declare(strict_types=1);

namespace App\Filament\Resources\Staff\Support;

use App\Filament\Resources\Members\Support\MemberFormSupport;
use App\Models\Staff;
use Filament\Schemas\Schema;

class StaffFormSupport
{
    /**
     * @return array<string, mixed>
     */
    public static function formState(Schema $form): array
    {
        return collect($form->getRawState())->all();
    }

    public static function generateEmployeeCode(): string
    {
        $latestIndex = Staff::query()
            ->withTrashed()
            ->where('employee_code', 'like', 'EMP%')
            ->get(['employee_code'])
            ->map(fn (Staff $staff): int => (int) preg_replace('/\D/', '', $staff->employee_code))
            ->max() ?? 0;

        do {
            $latestIndex++;
            $code = 'EMP'.str_pad((string) $latestIndex, 5, '0', STR_PAD_LEFT);
        } while (Staff::query()->withTrashed()->where('employee_code', $code)->exists());

        return $code;
    }

    public static function storeProfilePhoto(mixed $uploadedPath, string $userName): ?string
    {
        return MemberFormSupport::storeProfilePhoto($uploadedPath, $userName);
    }
}
