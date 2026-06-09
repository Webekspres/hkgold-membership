<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ContentType;
use App\Models\Content;
use App\Models\Media;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Content>
 */
class ContentFactory extends Factory
{
    protected $model = Content::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $type = fake()->randomElement(ContentType::cases());

        return [
            'type' => $type,
            'title' => match ($type) {
                ContentType::News => fake()->randomElement([
                    'Harga Emas Stabil di Awal Bulan',
                    'Promo Akhir Tahun HK GOLD VIP',
                    'Tips Merawat Perhiasan Emas',
                ]),
                ContentType::Event => 'Gathering Member HK GOLD VIP '.fake()->city(),
                ContentType::Exhibition => 'Pameran Koleksi Emas Eksklusif 2026',
                ContentType::Banner => 'Banner Utama Aplikasi Mobile',
            },
            'body' => fake('id_ID')->paragraphs(3, true),
            'location' => fake()->optional(0.6)->city(),
            'start_date' => fake()->optional(0.8)->dateTimeBetween('-1 month', '+3 months'),
            'end_date' => fake()->optional(0.6)->dateTimeBetween('+1 week', '+6 months'),
            'is_published' => true,
            'media_id' => Media::query()->whereDoesntHave('content')->inRandomOrder()->value('id')
                ?? Media::factory(),
        ];
    }
}
