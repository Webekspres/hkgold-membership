<?php

declare(strict_types=1);

namespace App\Filament\Resources\Contents\Support;

use App\Models\Content;
use App\Models\ContentCoverImage;
use App\Models\Media;
use Filament\Schemas\Schema;
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
        $baseUrl = Storage::disk('public')->url('');

        if (str_starts_with($media->file_url, $baseUrl)) {
            return ltrim(substr($media->file_url, strlen($baseUrl)), '/');
        }

        return 'content-covers/'.$media->file_name;
    }

    public static function storeCoverImage(mixed $uploadedPath, string $contentTitle): ?string
    {
        $path = is_array($uploadedPath) ? ($uploadedPath[array_key_first($uploadedPath)] ?? null) : $uploadedPath;

        if (blank($path)) {
            return null;
        }

        $disk = Storage::disk('public');

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
            'file_type' => $disk->mimeType($path) ?? 'application/octet-stream',
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
