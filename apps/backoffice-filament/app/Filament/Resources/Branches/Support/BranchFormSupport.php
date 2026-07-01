<?php

declare(strict_types=1);

namespace App\Filament\Resources\Branches\Support;

use App\Filament\Resources\Members\Support\MemberFormSupport;
use App\Models\Address;
use App\Models\Branch;
use App\Models\Village;
use Filament\Schemas\Schema;

class BranchFormSupport
{
    /**
     * @return array<string, mixed>
     */
    public static function formState(Schema $form): array
    {
        return collect($form->getRawState())->all();
    }

    public static function generateBranchCode(): string
    {
        $latestIndex = Branch::query()
            ->where('branch_code', 'like', 'HK%')
            ->get(['branch_code'])
            ->map(fn (Branch $branch): int => (int) preg_replace('/\D/', '', $branch->branch_code))
            ->max() ?? 0;

        do {
            $latestIndex++;
            $code = 'HK'.str_pad((string) $latestIndex, 2, '0', STR_PAD_LEFT);
        } while (Branch::query()->where('branch_code', $code)->exists());

        return $code;
    }

    /**
     * @param  array<string, mixed>  $state
     */
    public static function upsertAddress(array $state, ?Address $existing = null): ?string
    {
        return MemberFormSupport::syncAddress($state, $existing);
    }

    /**
     * @return array<string, mixed>
     */
    public static function addressState(Branch $branch): array
    {
        $branch->loadMissing([
            'normalizedAddress.village.subDistrict.city',
            'normalizedAddress.postalCode',
        ]);

        $address = $branch->normalizedAddress;
        $village = $address?->village;
        $subDistrict = $village?->subDistrict;
        $city = $subDistrict?->city;

        return [
            'province_id' => $city?->province_id,
            'city_id' => $subDistrict?->city_id,
            'sub_district_id' => $village?->sub_district_id,
            'village_id' => $address?->village_id,
            'postal_code_id' => $address?->postal_code_id,
            'street' => $address?->street,
        ];
    }

    /**
     * @param  array<string, mixed>  $state
     */
    public static function buildAddressString(array $state): ?string
    {
        $street = filled($state['street'] ?? null) ? (string) $state['street'] : null;

        if (blank($street) && blank($state['village_id'] ?? null)) {
            return null;
        }

        $parts = [];

        if (filled($street)) {
            $parts[] = $street;
        }

        if (filled($state['village_id'] ?? null)) {
            $village = Village::query()
                ->with(['subDistrict.city.province'])
                ->find($state['village_id']);

            if ($village !== null) {
                $parts[] = $village->nama;
                $parts[] = $village->subDistrict?->nama;
                $parts[] = $village->subDistrict?->city?->nama;
                $parts[] = $village->subDistrict?->city?->province?->nama;
            }
        }

        $parts = array_values(array_filter($parts, fn (?string $part): bool => filled($part)));

        return $parts !== [] ? implode(', ', $parts) : null;
    }
}
