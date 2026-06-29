<?php

declare(strict_types=1);

namespace App\Filament\Resources\CategoryRewards\Support;

use App\Models\CategoryReward;
use Illuminate\Support\Str;

class CategoryRewardFormSupport
{
    public static function slugFromName(string $name): string
    {
        return Str::slug($name);
    }

    /**
     * @return array<int, \Closure|string>
     */
    public static function nameValidationRules(?CategoryReward $record = null): array
    {
        return [
            'required',
            'string',
            'max:100',
            function (string $attribute, mixed $value, \Closure $fail) use ($record): void {
                if (! is_string($value) || blank($value)) {
                    return;
                }

                $slug = self::slugFromName($value);

                $exists = CategoryReward::query()
                    ->where('slug', $slug)
                    ->when(
                        $record !== null,
                        fn ($query) => $query->where('id', '!=', $record->id),
                    )
                    ->exists();

                if ($exists) {
                    $fail('Nama kategori sudah ada.');
                }
            },
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public static function prepareSaveData(array $data): array
    {
        $name = isset($data['name']) && is_string($data['name']) ? trim($data['name']) : '';

        return [
            ...$data,
            'name' => $name,
            'slug' => self::slugFromName($name),
        ];
    }
}
