<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\District;
use App\Models\PostalCode;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PostalCode>
 */
class PostalCodeFactory extends Factory
{
    protected $model = PostalCode::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'sub_district_id' => District::factory(),
            'city_id' => function (array $attributes): int {
                $districtId = $attributes['sub_district_id'];

                if ($districtId instanceof District) {
                    return (int) $districtId->city_id;
                }

                return (int) District::query()->findOrFail($districtId)->city_id;
            },
            'kodepos' => (string) fake()->unique()->numberBetween(10000, 99999),
        ];
    }
}
