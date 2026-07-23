<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Address;
use App\Models\Branch;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Branch>
 */
class BranchFactory extends Factory
{
    protected $model = Branch::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        static $branchIndex = 0;

        $branches = [
            ['code' => 'HK01', 'name' => 'HK Gold VIP Pontianak', 'address' => 'Jl. Gajah Mada No. 1, Pontianak'],
            ['code' => 'HK02', 'name' => 'HK Gold VIP Semarang', 'address' => 'Jl. Pemuda No. 10, Semarang'],
            ['code' => 'HK03', 'name' => 'HK Gold VIP Solo', 'address' => 'Jl. Slamet Riyadi No. 5, Solo'],
        ];

        $branch = $branches[$branchIndex % count($branches)];
        $branchIndex++;

        $addressId = Address::query()->inRandomOrder()->value('id');

        return [
            'address_id' => $addressId ?? Address::factory(),
            'branch_code' => $branch['code'].'-'.fake()->unique()->numerify('##'),
            'name' => $branch['name'],
            'address' => $branch['address'],
            'phone' => '021'.fake()->numerify('#######'),
            'location_url' => fake()->optional(0.8)->url(),
            'is_online_warehouse' => false,
        ];
    }
}
