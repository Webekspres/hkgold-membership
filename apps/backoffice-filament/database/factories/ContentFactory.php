<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ContentType;
use App\Models\Content;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

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
        $type = fake()->randomElement([ContentType::News, ContentType::Event]);
        $title = match ($type) {
            ContentType::News => fake()->randomElement([
                'Harga Emas Stabil di Awal Bulan',
                'Promo Akhir Tahun HK GOLD VIP',
                'Tips Merawat Perhiasan Emas',
            ]),
            ContentType::Event => 'Gathering Member HK GOLD VIP '.fake()->city(),
            default => fake()->sentence(4),
        };

        return [
            'type' => $type,
            'title' => $title,
            'slug' => Str::slug($title).'-'.fake()->unique()->numerify('###'),
            'body_content' => fake('id_ID')->paragraphs(3, true),
            'event_date' => $type === ContentType::Event ? fake()->dateTimeBetween('+1 week', '+3 months') : null,
            'is_active' => true,
        ];
    }
}
