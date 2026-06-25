<?php

declare(strict_types=1);

namespace App\Filament\Resources\Contents\Support;

use App\Models\Content;
use App\Models\ContentCoverImage;
use App\Models\Media;
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

    public static function storeCoverImage(mixed $uploadedPath, string $contentTitle): ?string
    {
        $path = is_array($uploadedPath) ? ($uploadedPath[array_key_first($uploadedPath)] ?? null) : $uploadedPath;

        if (blank($path)) {
            return null;
        }

        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk('r2');

        if (! $disk->exists($path)) {
            return null;
        }

        $fileUrl = $disk->url($path);

        $existingMediaId = Media::query()->where('file_url', $fileUrl)->value('id');

        if ($existingMediaId !== null) {
            return $existingMediaId;
        }

        $media = Media::query()->create([
            'caption' => 'content-cover_'.Str::slug($contentTitle, '_'),
            'file_name' => basename($path),
            'file_type' => $disk->mimeType($path) ?? 'image/jpeg',
            'file_url' => $fileUrl,
            'file_size' => $disk->size($path),
        ]);

        return $media->id;
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     */
    public static function syncCoverImages(Content $content, array $items): void
    {
        $previousMediaIds = $content->contentCoverImages()->pluck('media_id')->all();

        $content->contentCoverImages()->delete();

        $retainedMediaIds = [];

        foreach (array_values($items) as $sortOrder => $item) {
            $mediaId = self::resolveCoverMediaId(
                $item['image'] ?? null,
                $content->title,
                isset($item['media_id']) ? (string) $item['media_id'] : null,
            );

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
            if (! self::isMediaInUse((string) $mediaId)) {
                Media::query()->whereKey($mediaId)->delete();
            }
        }
    }

    public static function resolveCoverMediaId(mixed $uploadedPath, string $contentTitle, ?string $existingMediaId = null): ?string
    {
        if (is_array($uploadedPath) && isset($uploadedPath['public_url'])) {
            return self::storeCoverImageFromMetadata($uploadedPath, $contentTitle);
        }

        $path = is_array($uploadedPath) ? ($uploadedPath[array_key_first($uploadedPath)] ?? null) : $uploadedPath;

        if (blank($path) && filled($existingMediaId)) {
            return $existingMediaId;
        }

        if (blank($path)) {
            return null;
        }

        if ($existingMediaId !== null) {
            $media = Media::query()->find($existingMediaId);

            if ($media !== null && self::mediaToUploadPath($media) === $path) {
                return $existingMediaId;
            }
        }

        return self::storeCoverImage($path, $contentTitle);
    }

    /**
     * @param  array<string, mixed>  $uploadedPath
     */
    public static function storeCoverImageFromMetadata(array $uploadedPath, string $contentTitle): ?string
    {
        $publicUrl = isset($uploadedPath['public_url']) ? (string) $uploadedPath['public_url'] : null;

        if (blank($publicUrl)) {
            return null;
        }

        $fileName = isset($uploadedPath['file_name']) && filled($uploadedPath['file_name'])
            ? (string) $uploadedPath['file_name']
            : basename((string) parse_url($publicUrl, PHP_URL_PATH));

        $media = Media::query()->firstOrCreate(
            ['file_url' => $publicUrl],
            [
                'caption' => 'content-cover_'.Str::slug($contentTitle, '_'),
                'file_name' => $fileName,
                'file_type' => (string) ($uploadedPath['file_type'] ?? 'image/webp'),
                'file_size' => (int) ($uploadedPath['file_size'] ?? 0),
            ],
        );

        return $media->id;
    }

    /**
     * @return array{key: string, public_url: string, file_name: string, file_size: int, file_type: string}
     */
    public static function mediaToUploaderState(Media $media): array
    {
        $path = ltrim((string) parse_url($media->file_url, PHP_URL_PATH), '/');

        return [
            'key' => $path,
            'public_url' => $media->file_url,
            'file_name' => $media->file_name,
            'file_size' => (int) $media->file_size,
            'file_type' => $media->file_type ?: 'image/webp',
        ];
    }

    public static function isMediaInUse(string $mediaId): bool
    {
        if (ContentCoverImage::query()->where('media_id', $mediaId)->exists()) {
            return true;
        }

        return Media::query()
            ->whereKey($mediaId)
            ->whereHas('user')
            ->exists();
    }
}
