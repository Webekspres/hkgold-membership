<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Address;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\DB;
use RuntimeException;

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
        $villageId = DB::table('villages')->value('id');
        $postalCodeId = DB::table('postal_codes')->value('id');

        if ($villageId === null || $postalCodeId === null) {
            throw new RuntimeException('Location data must be seeded before creating addresses.');
        }

        $streetTypes = ['Jl.', 'Jalan', 'Komplek', 'Gang'];
        $streetNames = ['Sudirman', 'Gajah Mada', 'Ahmad Yani', 'Merdeka', 'Diponegoro'];

        return [
            'village_id' => $villageId,
            'postal_code_id' => $postalCodeId,
            'street' => sprintf(
                '%s %s No. %d',
                fake()->randomElement($streetTypes),
                fake()->randomElement($streetNames),
                fake()->numberBetween(1, 250)
            ),
        ];
    }
}
