<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\ContentStatus;
use App\Enums\ContentType;
use App\Models\Content;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ContentSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $items = [
            [ContentType::News, 'Harga Emas Stabil di Awal Bulan'],
            [ContentType::News, 'Promo Akhir Tahun HK GOLD VIP'],
            [ContentType::Event, 'Gathering Member Pontianak 2026'],
            [ContentType::Event, 'Workshop Investasi Emas Pemula'],
            [ContentType::News, 'Tips Merawat Perhiasan Emas'],
            [ContentType::Event, 'Grand Opening Cabang Solo'],
            [ContentType::News, 'Program Loyalitas Sapphire Resmi Diluncurkan'],
            [ContentType::Event, 'Charity Gold Run HK GOLD VIP'],
        ];

        foreach ($items as [$type, $title]) {
            Content::query()->firstOrCreate(
                ['slug' => Str::slug($title)],
                [
                    'type' => $type,
                    'title' => $title,
                    'body_content' => fake('id_ID')->paragraphs(2, true),
                    'event_date' => $type === ContentType::Event ? fake()->dateTimeBetween('+1 week', '+3 months') : null,
                    'status' => ContentStatus::Published,
                    'is_staged' => false,
                ],
            );
        }
    }
}
