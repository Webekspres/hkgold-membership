<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Media;
use App\Models\Reward;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Media>
 */
class MediaFactory extends Factory
{
    protected $model = Media::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $fileName = fake()->randomElement([
            'cincin-emas-22k.jpg',
            'kalung-antam-1.jpg',
            'gelang-tiffany.jpg',
            'liontin-bulan.jpg',
            'profile-member.jpg',
            'banner-promo-ramadhan.jpg',
        ]);

        return [
            'reward_id' => null,
            'caption' => fake('id_ID')->sentence(6),
            'file_name' => $fileName,
            'file_type' => 'image/jpeg',
            'file_url' => 'https://cdn.hkgoldvip.id/media/'.Str::uuid().'/'.$fileName,
            'file_size' => fake()->numberBetween(120_000, 2_500_000),
        ];
    }

    public function forReward(?Reward $reward = null): static
    {
        return $this->state(fn (): array => [
            'reward_id' => $reward?->id ?? Reward::factory(),
        ]);
    }
}
