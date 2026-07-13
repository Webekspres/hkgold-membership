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
            [ContentType::News, 'Harga Emas Stabil di Awal Bulan', null, null],
            [ContentType::News, 'Promo Akhir Tahun HK GOLD VIP', null, null],
            [
                ContentType::Event,
                'Gathering Member Pontianak 2026',
                'Ballroom Hotel Mercure Pontianak, Jl. Jenderal Ahmad Yani No. 91, Pontianak',
                'https://maps.google.com/?q=Hotel+Mercure+Pontianak',
            ],
            [
                ContentType::Event,
                'Workshop Investasi Emas Pemula',
                'Gedung Serbaguna HK GOLD VIP Semarang, Jl. Pandanaran No. 12, Semarang',
                'https://maps.google.com/?q=Pandanaran+Semarang',
            ],
            [ContentType::News, 'Tips Merawat Perhiasan Emas', null, null],
            [
                ContentType::Event,
                'Grand Opening Cabang Solo',
                'HK GOLD VIP Solo, Jl. Slamet Riyadi No. 250, Surakarta',
                'https://maps.google.com/?q=Slamet+Riyadi+Surakarta',
            ],
            [ContentType::News, 'Program Loyalitas Sapphire Resmi Diluncurkan', null, null],
            [
                ContentType::Event,
                'Charity Gold Run HK GOLD VIP',
                'Taman Bungkul Surabaya, Jl. Raya Darmo, Surabaya',
                'https://maps.google.com/?q=Taman+Bungkul+Surabaya',
            ],
        ];

        foreach ($items as [$type, $title, $locationAddress, $locationUrl]) {
            Content::query()->updateOrCreate(
                ['slug' => Str::slug($title)],
                [
                    'type' => $type,
                    'title' => $title,
                    'body_content' => fake('id_ID')->paragraphs(2, true),
                    'event_date' => $type === ContentType::Event ? fake()->dateTimeBetween('+1 week', '+3 months') : null,
                    'location_address' => $locationAddress,
                    'location_url' => $locationUrl,
                    'status' => ContentStatus::Published,
                    'is_staged' => false,
                ],
            );
        }
    }
}
