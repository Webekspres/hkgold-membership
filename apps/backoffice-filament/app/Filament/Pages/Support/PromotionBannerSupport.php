<?php

declare(strict_types=1);

namespace App\Filament\Pages\Support;

use App\Filament\Resources\Contents\Support\ContentFormSupport;
use App\Models\Media;
use App\Models\PromotionBanner;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PromotionBannerSupport
{
    /**
     * @param  array<int, array<string, mixed>>  $items
     */
    public static function syncBanners(array $items): void
    {
        DB::transaction(function () use ($items): void {
            $existingIds = PromotionBanner::query()->pluck('id')->all();
            $submittedIds = [];

            foreach (array_values($items) as $sortOrder => $item) {
                if (! is_array($item)) {
                    continue;
                }

                $name = isset($item['name']) && filled($item['name']) ? (string) $item['name'] : 'Banner';
                $imagePath = is_string($item['image'] ?? null) ? $item['image'] : null;
                $mediaId = self::storeBannerImage($imagePath ?? '', $name);
                $linkUrl = isset($item['link_url']) && filled($item['link_url'])
                    ? (string) $item['link_url']
                    : null;

                if ($mediaId === null) {
                    continue;
                }

                $bannerId = $item['id'] ?? null;
                $isActive = (bool) ($item['is_active'] ?? true);

                if (filled($bannerId)) {
                    $banner = PromotionBanner::query()->find($bannerId);

                    if ($banner === null) {
                        continue;
                    }

                    $previousMediaId = $banner->media_id;

                    $banner->update([
                        'name' => $name,
                        'media_id' => $mediaId,
                        'link_url' => $linkUrl,
                        'is_active' => $isActive,
                        'sort_order' => $sortOrder,
                    ]);

                    if ($previousMediaId !== $mediaId) {
                        self::purgeMediaForBanner($previousMediaId);
                    }

                    $submittedIds[] = $banner->id;

                    continue;
                }

                $banner = PromotionBanner::query()->create([
                    'name' => $name,
                    'media_id' => $mediaId,
                    'link_url' => $linkUrl,
                    'is_active' => $isActive,
                    'sort_order' => $sortOrder,
                ]);

                $submittedIds[] = $banner->id;
            }

            $orphanedIds = array_diff($existingIds, $submittedIds);

            foreach ($orphanedIds as $orphanedId) {
                $banner = PromotionBanner::query()->find($orphanedId);

                if ($banner === null) {
                    continue;
                }

                $mediaId = $banner->media_id;
                $banner->delete();
                self::purgeMediaForBanner($mediaId);
            }
        });
    }

    public static function storeBannerImage(string $path, string $name): ?string
    {
        if (blank($path)) {
            return null;
        }

        if (str_starts_with($path, 'banners/')) {
            /** @var FilesystemAdapter $disk */
            $disk = Storage::disk('r2');
            $fileUrl = $disk->url($path);

            $existingMediaId = Media::query()->where('file_url', $fileUrl)->value('id');

            if ($existingMediaId !== null) {
                return $existingMediaId;
            }
        }

        $path = self::resolveBannerImagePath($path);

        if ($path === null) {
            return null;
        }

        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk('r2');

        if (! str_ends_with(strtolower($path), '.webp')) {
            $convertedPath = ContentFormSupport::convertR2ObjectToWebp($disk, $path);

            if ($convertedPath === null) {
                return null;
            }

            $path = $convertedPath;
        }

        $path = self::moveToImages($disk, $path);
        $fileUrl = $disk->url($path);

        $existingMediaId = Media::query()->where('file_url', $fileUrl)->value('id');

        if ($existingMediaId !== null) {
            return $existingMediaId;
        }

        $media = Media::query()->create([
            'caption' => 'promotion-banner_'.Str::slug($name, '_'),
            'file_name' => basename($path),
            'file_type' => 'image/webp',
            'file_url' => $fileUrl,
            'file_size' => $disk->size($path),
        ]);

        return $media->id;
    }

    /**
     * @return non-empty-string|null
     */
    private static function resolveBannerImagePath(string $path): ?string
    {
        /** @var FilesystemAdapter $r2 */
        $r2 = Storage::disk('r2');
        /** @var FilesystemAdapter $staging */
        $staging = Storage::disk('content_staging');

        if ($r2->exists($path)) {
            return $path;
        }

        $stagingPath = str_starts_with($path, 'temp/') ? $path : 'temp/'.ltrim($path, '/');

        if (! $staging->exists($stagingPath)) {
            return null;
        }

        $r2TempPath = 'temp/'.basename($stagingPath);

        $r2->put($r2TempPath, $staging->get($stagingPath), [
            'ContentType' => self::mimeTypeForPath($stagingPath),
        ]);

        $staging->delete($stagingPath);

        return $r2TempPath;
    }

    private static function mimeTypeForPath(string $path): string
    {
        return match (strtolower(pathinfo($path, PATHINFO_EXTENSION))) {
            'webp' => 'image/webp',
            'png' => 'image/png',
            'gif' => 'image/gif',
            default => 'image/jpeg',
        };
    }

    /**
     * @return non-empty-string
     */
    private static function moveToImages(FilesystemAdapter $disk, string $path): string
    {
        if (! str_starts_with($path, 'temp/')) {
            return $path;
        }

        $permanentPath = 'banners/'.basename($path);

        if ($disk->exists($path)) {
            $disk->move($path, $permanentPath);
        }

        return $permanentPath;
    }

    public static function purgeMediaForBanner(string $mediaId): void
    {
        if (ContentFormSupport::isMediaInUse($mediaId)) {
            return;
        }

        $media = Media::query()->find($mediaId);

        if ($media === null) {
            return;
        }

        ContentFormSupport::deleteMediaFileFromStorage($media);
        $media->delete();
    }
}
