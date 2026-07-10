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
            ['code' => 'HKG-PTK', 'name' => 'HK Gold VIP Pontianak', 'lat' => -0.0263, 'lng' => 109.3425],
            ['code' => 'HKG-SMG', 'name' => 'HK Gold VIP Semarang', 'lat' => -6.9667, 'lng' => 110.4167],
            ['code' => 'HKG-SLO', 'name' => 'HK Gold VIP Solo', 'lat' => -7.5667, 'lng' => 110.8167],
            ['code' => 'HKG-YGY', 'name' => 'HK Gold VIP Yogyakarta', 'lat' => -7.7956, 'lng' => 110.3695],
            ['code' => 'HKG-SBY', 'name' => 'HK Gold VIP Surabaya', 'lat' => -7.2575, 'lng' => 112.7521],
            ['code' => 'HKG-BDG', 'name' => 'HK Gold VIP Bandung', 'lat' => -6.9175, 'lng' => 107.6191],
            ['code' => 'HKG-JKT', 'name' => 'HK Gold VIP Jakarta Pusat', 'lat' => -6.1751, 'lng' => 106.8650],
            ['code' => 'HKG-MDN', 'name' => 'HK Gold VIP Medan', 'lat' => 3.5952, 'lng' => 98.6722],
            ['code' => 'HKG-PLB', 'name' => 'HK Gold VIP Palembang', 'lat' => -2.9761, 'lng' => 104.7754],
            ['code' => 'HKG-MKS', 'name' => 'HK Gold VIP Makassar', 'lat' => -5.1477, 'lng' => 119.4327],
        ];

        $branch = $branches[$branchIndex % count($branches)];
        $branchIndex++;

        return [
            'address_id' => Address::factory(),
            'code' => $branch['code'].'-'.fake()->unique()->numerify('##'),
            'name' => $branch['name'],
            'phone' => '021'.fake()->numerify('#######'),
            'latitude' => $branch['lat'],
            'longitude' => $branch['lng'],
            'is_active' => true,
            'open_time' => '08:00',
            'close_time' => '17:00',
        ];
    }
}
