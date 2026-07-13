<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\TierBenefit;
use App\Models\TierMember;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TierBenefit>
 */
class TierBenefitFactory extends Factory
{
    protected $model = TierBenefit::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $benefits = [
            ['title' => 'Diskon ongkos pembuatan', 'description' => 'Potongan biaya jasa pembuatan perhiasan di seluruh cabang.'],
            ['title' => 'Prioritas layanan toko', 'description' => 'Antrian prioritas saat transaksi di cabang fisik.'],
            ['title' => 'Voucher ulang tahun', 'description' => 'Voucher khusus yang dikirim setiap tanggal ulang tahun member.'],
            ['title' => 'Akses event eksklusif', 'description' => 'Undangan acara dan preview koleksi terbatas.'],
            ['title' => 'Gratis cuci emas', 'description' => 'Layanan cuci emas gratis sesuai kuota tier.'],
            ['title' => 'Bonus poin transaksi', 'description' => 'Tambahan poin pada transaksi tertentu sesuai ketentuan.'],
        ];

        $benefit = fake()->randomElement($benefits);

        return [
            'tier_member_id' => TierMember::query()->inRandomOrder()->value('id') ?? 1,
            'title' => $benefit['title'],
            'description' => $benefit['description'],
            'sort_order' => fake()->numberBetween(0, 10),
            'is_active' => true,
        ];
    }
}
