<?php

declare(strict_types=1);

namespace App\Filament\Resources\Rewards\Support;

use App\Filament\Resources\Contents\Support\ContentFormSupport;
use App\Models\ContentCoverImage;
use App\Models\Media;
use App\Models\PromotionBanner;
use App\Models\Reward;
use App\Models\RewardImage;
use Filament\Forms\Components\RichEditor\RichContentRenderer;
use Filament\Schemas\Schema;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class RewardFormSupport
{
    /**
     * @return array<string, mixed>
     */
    public static function formState(Schema $form): array
    {
        return collect($form->getRawState())->all();
    }

    public static function normalizeDescription(mixed $body): string
    {
        if ($body === null || $body === '') {
            return '';
        }

        if (is_string($body)) {
            return $body;
        }

        if ($body instanceof Htmlable) {
            return $body->toHtml();
        }

        if (is_array($body)) {
            return RichContentRenderer::make($body)->toHtml();
        }

        return '';
    }

    public static function mediaToUploadPath(Media $media): string
    {
        $r2BaseUrl = rtrim((string) config('filesystems.disks.r2.url'), '/');

        if (filled($r2BaseUrl) && str_starts_with($media->file_url, $r2BaseUrl.'/')) {
            return ltrim(substr($media->file_url, strlen($r2BaseUrl)), '/');
        }

        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk('public');
        $publicBaseUrl = $disk->url('');

        if (str_starts_with($media->file_url, $publicBaseUrl)) {
            return ltrim(substr($media->file_url, strlen($publicBaseUrl)), '/');
        }

        return 'catalog/'.$media->file_name;
    }

    public static function storeImage(string $path, string $sku): ?string
    {
        if (blank($path)) {
            return null;
        }

        $path = self::resolveImagePath($path);

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

        $path = self::moveFromTempToPermanent($disk, $path);

        $fileUrl = $disk->url($path);

        $existingMediaId = Media::query()->where('file_url', $fileUrl)->value('id');

        if ($existingMediaId !== null) {
            return $existingMediaId;
        }

        $media = Media::query()->create([
            'caption' => 'reward-img_'.Str::slug($sku, '_'),
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
    private static function resolveImagePath(string $path): ?string
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
     * @param  array<int, string>  $paths
     */
    public static function syncImages(Reward $reward, array $paths): void
    {
        $previousMediaIds = $reward->rewardImages()->pluck('media_id')->all();

        $reward->rewardImages()->delete();

        $retainedMediaIds = [];

        foreach (array_values($paths) as $sortOrder => $path) {
            if (! is_string($path) || blank($path)) {
                continue;
            }

            $mediaId = self::storeImage($path, $reward->sku);

            if ($mediaId === null) {
                continue;
            }

            RewardImage::query()->create([
                'reward_id' => $reward->id,
                'media_id' => $mediaId,
                'sort_order' => $sortOrder,
            ]);

            $retainedMediaIds[] = $mediaId;
        }

        $orphanedMediaIds = array_diff($previousMediaIds, $retainedMediaIds);

        foreach ($orphanedMediaIds as $mediaId) {
            self::purgeUnusedMedia((string) $mediaId);
        }
    }

    public static function purgeUnusedMedia(string $mediaId): void
    {
        if (self::isMediaInUse($mediaId)) {
            return;
        }

        $media = Media::query()->find($mediaId);

        if ($media === null) {
            return;
        }

        self::deleteMediaFileFromStorage($media);
        $media->delete();
    }

    public static function deleteMediaFileFromStorage(Media $media): void
    {
        $r2BaseUrl = rtrim((string) config('filesystems.disks.r2.url'), '/');

        if (filled($r2BaseUrl) && str_starts_with($media->file_url, $r2BaseUrl.'/')) {
            Storage::disk('r2')->delete(self::mediaToUploadPath($media));

            return;
        }

        /** @var FilesystemAdapter $publicDisk */
        $publicDisk = Storage::disk('public');
        $publicBaseUrl = $publicDisk->url('');

        if (str_starts_with($media->file_url, $publicBaseUrl)) {
            $publicDisk->delete(self::mediaToUploadPath($media));
        }
    }

    public static function isMediaInUse(string $mediaId, ?string $exceptRewardId = null): bool
    {
        if (RewardImage::query()
            ->where('media_id', $mediaId)
            ->when(
                $exceptRewardId !== null,
                fn ($query) => $query->where('reward_id', '!=', $exceptRewardId),
            )
            ->exists()) {
            return true;
        }

        if (ContentCoverImage::query()->where('media_id', $mediaId)->exists()) {
            return true;
        }

        if (PromotionBanner::query()->where('media_id', $mediaId)->exists()) {
            return true;
        }

        return Media::query()
            ->whereKey($mediaId)
            ->whereHas('user')
            ->exists();
    }

    /**
     * @return non-empty-string
     */
    private static function moveFromTempToPermanent(FilesystemAdapter $disk, string $path): string
    {
        if (! str_starts_with($path, 'temp/')) {
            return $path;
        }

        $permanentPath = 'catalog/'.basename($path);

        if ($disk->exists($path)) {
            $disk->move($path, $permanentPath);
        }

        return $permanentPath;
    }
}
