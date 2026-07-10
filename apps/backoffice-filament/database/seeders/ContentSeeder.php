<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\ContentType;
use App\Models\Content;
use App\Models\Media;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ContentSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        if (Media::query()->count() === 0) {
            $this->call(MediaSeeder::class);
        }

        $mediaIds = Media::query()
            ->whereDoesntHave('user')
            ->whereDoesntHave('content')
            ->pluck('id')
            ->all();

        $items = [
            [ContentType::News, 'Harga Emas Stabil di Awal Bulan'],
            [ContentType::News, 'Promo Akhir Tahun HK GOLD VIP'],
            [ContentType::Event, 'Gathering Member Pontianak 2026'],
            [ContentType::Event, 'Workshop Investasi Emas Pemula'],
            [ContentType::Exhibition, 'Pameran Koleksi Emas Eksklusif'],
            [ContentType::Banner, 'Banner Utama Aplikasi Mobile'],
            [ContentType::News, 'Tips Merawat Perhiasan Emas'],
            [ContentType::Event, 'Grand Opening Cabang Solo'],
            [ContentType::Exhibition, 'Showcase Perhiasan Antam Terbaru'],
            [ContentType::Banner, 'Banner Promo Ramadhan'],
            [ContentType::News, 'Program Loyalitas Sapphire Resmi Diluncurkan'],
            [ContentType::Event, 'Charity Gold Run HK GOLD VIP'],
        ];

        foreach ($items as $index => [$type, $title]) {
            if (! array_key_exists($index, $mediaIds)) {
                break;
            }

            Content::query()->firstOrCreate(
                ['title' => $title],
                [
                    'type' => $type,
                    'body' => fake('id_ID')->paragraphs(2, true),
                    'location' => fake()->optional(0.5)->city(),
                    'start_date' => fake()->dateTimeBetween('-1 month', '+2 months'),
                    'end_date' => fake()->optional(0.5)->dateTimeBetween('+1 week', '+4 months'),
                    'is_published' => true,
                    'media_id' => $mediaIds[$index],
                ],
            );
        }
    }
}
