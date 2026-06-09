<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Media;
use App\Models\Reward;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class MediaSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        if (Reward::query()->count() === 0) {
            $this->call(RewardSeeder::class);
        }

        $rewardIds = Reward::query()->pluck('id')->all();

        $files = [
            'cincin-emas-22k.jpg',
            'kalung-antam-1.jpg',
            'gelang-tiffany.jpg',
            'liontin-bulan.jpg',
            'banner-promo-ramadhan.jpg',
            'profile-member-01.jpg',
            'profile-member-02.jpg',
            'voucher-belanja.jpg',
            'tas-merchandise.jpg',
            'pameran-emas-2026.jpg',
            'event-gathering.jpg',
            'news-harga-emas.jpg',
        ];

        foreach ($files as $index => $fileName) {
            Media::query()->firstOrCreate(
                ['file_name' => $fileName],
                [
                    'reward_id' => $index < 6 ? $rewardIds[$index % count($rewardIds)] : null,
                    'caption' => 'Aset media HK GOLD VIP - '.$fileName,
                    'file_type' => 'image/jpeg',
                    'file_url' => 'https://cdn.hkgoldvip.id/media/'.Str::uuid().'/'.$fileName,
                    'file_size' => fake()->numberBetween(150_000, 1_800_000),
                ],
            );
        }
    }
}
