<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\BranchImage;
use App\Models\Media;
use Illuminate\Database\Seeder;

class BranchImageSeeder extends Seeder
{
    /**
     * Seed dummy branch images using placeholder URLs.
     * Each non-online branch gets 2–3 sample photo records.
     */
    public function run(): void
    {
        $placeholderPhotos = [
            [
                'file_name' => 'branch-exterior-1.jpg',
                'caption' => 'Tampak depan cabang',
                'file_url' => 'https://placehold.co/1200x675/F5A623/FFFFFF?text=Tampak+Depan',
            ],
            [
                'file_name' => 'branch-interior-1.jpg',
                'caption' => 'Area dalam cabang',
                'file_url' => 'https://placehold.co/1200x675/4A90E2/FFFFFF?text=Interior+Cabang',
            ],
            [
                'file_name' => 'branch-counter-1.jpg',
                'caption' => 'Area counter pelayanan',
                'file_url' => 'https://placehold.co/1200x675/7ED321/FFFFFF?text=Counter+Cabang',
            ],
        ];

        $branches = Branch::where('is_online_warehouse', false)->get();

        foreach ($branches as $branch) {
            foreach ($placeholderPhotos as $index => $photo) {
                $media = Media::create([
                    'caption' => $photo['caption'].' - '.$branch->name,
                    'file_name' => $photo['file_name'],
                    'file_type' => 'image/jpeg',
                    'file_url' => $photo['file_url'],
                    'file_size' => 102400,
                ]);

                BranchImage::create([
                    'branch_id' => $branch->id,
                    'media_id' => $media->id,
                    'sort_order' => $index,
                ]);
            }
        }
    }
}
