<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\TierStatus;
use App\Models\TierBenefit;
use App\Models\TierMember;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TierMemberSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $tiers = [
            [TierStatus::Silver, 0, 1000],
            [TierStatus::Gold, 1001, 2000],
            [TierStatus::Platinum, 2001, 4000],
            [TierStatus::Elite, 4001, 99999],
        ];

        foreach ($tiers as [$tier, $minPoints, $maxPoints]) {
            DB::table('tier_members')->updateOrInsert(
                ['tier_code' => $tier->value],
                [
                    'min_points' => $minPoints,
                    'max_points' => $maxPoints,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            );
        }

        $this->seedBenefits();
    }

    private function seedBenefits(): void
    {
        $benefitsByTier = [
            TierStatus::Silver->value => [
                ['title' => 'Poin setiap transaksi', 'description' => 'Dapatkan poin dari setiap pembelian sesuai aturan konversi.'],
                ['title' => 'Notifikasi promo', 'description' => 'Terima info promo dan event melalui aplikasi.'],
            ],
            TierStatus::Gold->value => [
                ['title' => 'Diskon ongkos pembuatan', 'description' => 'Potongan biaya jasa pembuatan perhiasan di cabang.'],
                ['title' => 'Voucher ulang tahun', 'description' => 'Voucher khusus setiap tanggal ulang tahun member.'],
                ['title' => 'Prioritas layanan toko', 'description' => 'Antrian prioritas saat transaksi di cabang fisik.'],
            ],
            TierStatus::Platinum->value => [
                ['title' => 'Gratis cuci emas', 'description' => 'Layanan cuci emas gratis sesuai kuota tier.'],
                ['title' => 'Bonus poin transaksi', 'description' => 'Tambahan poin pada transaksi tertentu sesuai ketentuan.'],
                ['title' => 'Akses event eksklusif', 'description' => 'Undangan acara dan preview koleksi terbatas.'],
            ],
            TierStatus::Elite->value => [
                ['title' => 'Personal shopping assistant', 'description' => 'Pendampingan khusus untuk pemilihan koleksi premium.'],
                ['title' => 'Undangan private sale', 'description' => 'Akses penjualan privat sebelum dibuka ke publik.'],
                ['title' => 'Benefit VIP lengkap', 'description' => 'Semua benefit tier di bawahnya plus privilege Elite.'],
            ],
        ];

        foreach ($benefitsByTier as $tierCode => $benefits) {
            $tierMember = TierMember::query()->where('tier_code', $tierCode)->first();

            if ($tierMember === null) {
                continue;
            }

            if ($tierMember->tierBenefits()->exists()) {
                continue;
            }

            foreach ($benefits as $index => $benefit) {
                TierBenefit::query()->create([
                    'tier_member_id' => $tierMember->id,
                    'title' => $benefit['title'],
                    'description' => $benefit['description'],
                    'sort_order' => $index,
                    'is_active' => true,
                ]);
            }
        }
    }
}
