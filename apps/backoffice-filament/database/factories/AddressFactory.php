<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Address;
use App\Models\PostalCode;
use App\Models\Village;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Address>
 */
class AddressFactory extends Factory
{
    protected $model = Address::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $streetTypes = ['Jl.', 'Jalan', 'Komplek', 'Gang'];
        $streetNames = [
            'Sudirman',
            'Thamrin',
            'Gajah Mada',
            'Ahmad Yani',
            'Pahlawan',
            'Merdeka',
            'Diponegoro',
            'Kartini',
            'Pemuda',
            'Hasanuddin',
        ];

        $street = sprintf(
            '%s %s No. %d',
            fake()->randomElement($streetTypes),
            fake()->randomElement($streetNames),
            fake()->numberBetween(1, 250)
        );

        $villageIds = Village::query()->pluck('id')->all();
        $postalCodeIds = PostalCode::query()->pluck('id')->all();

        return [
            'village_id' => $villageIds !== []
                ? fake()->randomElement($villageIds)
                : Village::factory(),
            'postal_code_id' => $postalCodeIds !== []
                ? fake()->randomElement($postalCodeIds)
                : PostalCode::factory(),
            'street' => $street,
        ];
    }
}
