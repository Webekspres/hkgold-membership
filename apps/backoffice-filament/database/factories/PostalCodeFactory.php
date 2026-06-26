<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\PostalCode;
use App\Models\SubDistrict;
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
            'sub_district_id' => SubDistrict::factory(),
            'city_id' => function (array $attributes): int {
                $subDistrictId = $attributes['sub_district_id'];

                if ($subDistrictId instanceof SubDistrict) {
                    return (int) $subDistrictId->city_id;
                }

                return (int) SubDistrict::query()->findOrFail($subDistrictId)->city_id;
            },
            'kodepos' => (string) fake()->unique()->numerify('#####'),
        ];
    }
}
