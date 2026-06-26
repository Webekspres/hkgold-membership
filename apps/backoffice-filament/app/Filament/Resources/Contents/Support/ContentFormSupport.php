<?php

declare(strict_types=1);

namespace App\Filament\Resources\Contents\Support;

use App\Models\Content;
use App\Models\ContentCoverImage;
use App\Models\Media;
use App\Models\PromotionBanner;
use Filament\Forms\Components\RichEditor\RichContentRenderer;
use Filament\Schemas\Schema;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ContentFormSupport
{
    /**
     * @return array<string, mixed>
     */
    public static function formState(Schema $form): array
    {
        return collect($form->getRawState())->all();
    }

    public static function normalizeBodyContent(mixed $body): string
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

    public static function generateSlug(string $title, ?string $ignoreContentId = null): string
    {
        $baseSlug = Str::slug($title);
        $slug = $baseSlug;
        $suffix = 2;

        while (Content::query()
            ->where('slug', $slug)
            ->when(
                $ignoreContentId !== null,
                fn ($query) => $query->where('id', '!=', $ignoreContentId),
            )
            ->exists()) {
            $slug = $baseSlug.'-'.$suffix;
            $suffix++;
        }

        return $slug;
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

        return 'contents/'.$media->file_name;
    }

    public static function storeCoverImage(string $path, string $contentTitle): ?string
    {
        if (blank($path)) {
            return null;
        }

        $path = self::resolveCoverImagePath($path);

        if ($path === null) {
            return null;
        }

        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk('r2');

        if (! str_ends_with(strtolower($path), '.webp')) {
            $convertedPath = self::convertR2ObjectToWebp($disk, $path);

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
            'caption' => 'content-cover_'.Str::slug($contentTitle, '_'),
            'file_name' => basename($path),
            'file_type' => 'image/webp',
            'file_url' => $fileUrl,
            'file_size' => $disk->size($path),
        ]);

        return $media->id;
    }

    /**
     * Upload local staging files to R2, or return existing R2 paths.
     *
     * @return non-empty-string|null
     */
    private static function resolveCoverImagePath(string $path): ?string
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
    public static function syncCoverImages(Content $content, array $paths): void
    {
        $previousMediaIds = $content->contentCoverImages()->pluck('media_id')->all();

        $content->contentCoverImages()->delete();

        $retainedMediaIds = [];

        foreach (array_values($paths) as $sortOrder => $path) {
            if (! is_string($path) || blank($path)) {
                continue;
            }

            $mediaId = self::storeCoverImage($path, $content->title);

            if ($mediaId === null) {
                continue;
            }

            ContentCoverImage::query()->create([
                'content_id' => $content->id,
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

    public static function isMediaInUse(string $mediaId, ?string $exceptContentId = null): bool
    {
        if (ContentCoverImage::query()
            ->where('media_id', $mediaId)
            ->when(
                $exceptContentId !== null,
                fn ($query) => $query->where('content_id', '!=', $exceptContentId),
            )
            ->exists()) {
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

        $permanentPath = 'contents/'.basename($path);

        if ($disk->exists($path)) {
            $disk->move($path, $permanentPath);
        }

        return $permanentPath;
    }

    /**
     * @return non-empty-string|null
     */
    public static function convertR2ObjectToWebp(FilesystemAdapter $disk, string $path): ?string
    {
        if (! function_exists('imagecreatefromstring') || ! function_exists('imagewebp')) {
            return null;
        }

        $contents = $disk->get($path);
        $source = @imagecreatefromstring($contents);

        if (! $source instanceof \GdImage) {
            return null;
        }

        $width = imagesx($source);
        $height = imagesy($source);

        if ($width <= 0 || $height <= 0) {
            imagedestroy($source);

            return null;
        }

        $targetWidth = min($width, 1200);
        $targetHeight = max(1, (int) round($height * ($targetWidth / $width)));

        $canvas = imagecreatetruecolor($targetWidth, $targetHeight);

        if ($canvas === false) {
            imagedestroy($source);

            return null;
        }

        imagealphablending($canvas, true);
        imagesavealpha($canvas, true);
        imagecopyresampled($canvas, $source, 0, 0, 0, 0, $targetWidth, $targetHeight, $width, $height);
        imagedestroy($source);

        ob_start();
        imagewebp($canvas, null, 82);
        $webpContents = ob_get_clean();
        imagedestroy($canvas);

        if (! is_string($webpContents) || $webpContents === '') {
            return null;
        }

        $directory = dirname($path);
        $newPath = ($directory !== '.' ? $directory.'/' : '').Str::uuid()->toString().'.webp';

        $disk->put($newPath, $webpContents, [
            'ContentType' => 'image/webp',
        ]);

        $disk->delete($path);

        return $newPath;
    }
}
