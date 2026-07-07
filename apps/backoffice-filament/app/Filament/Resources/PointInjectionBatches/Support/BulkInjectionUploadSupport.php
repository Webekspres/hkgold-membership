<?php

declare(strict_types=1);

namespace App\Filament\Resources\PointInjectionBatches\Support;

use App\Models\Media;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use InvalidArgumentException;
use RuntimeException;

class BulkInjectionUploadSupport
{
    private const ALLOWED_EXTENSIONS = ['xlsx', 'xls', 'csv'];

    private const ALLOWED_MIME_TYPES = [
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'application/vnd.ms-excel',
        'text/csv',
        'text/plain',
        'application/csv',
        'application/octet-stream',
    ];

    /**
     * Move spreadsheet from content_staging to r2/imports/ and create a Media record.
     */
    public function storeSpreadsheet(mixed $uploadedPath): string
    {
        $stagingPath = $this->normalizeUploadedPath($uploadedPath);

        if ($stagingPath === null) {
            throw new InvalidArgumentException('File spreadsheet wajib diunggah.');
        }

        $this->assertAllowedSpreadsheet($stagingPath, Storage::disk('content_staging'));

        /** @var FilesystemAdapter $staging */
        $staging = Storage::disk('content_staging');
        /** @var FilesystemAdapter $r2 */
        $r2 = Storage::disk('r2');

        $filename = basename($stagingPath);
        $permanentPath = 'imports/'.Str::uuid().'/'.$filename;
        $mimeType = $this->mimeTypeForPath($stagingPath);

        $r2->put($permanentPath, $staging->get($stagingPath), [
            'ContentType' => $mimeType,
        ]);

        $staging->delete($stagingPath);

        $fileUrl = $r2->url($permanentPath);

        $existingMediaId = Media::query()->where('file_url', $fileUrl)->value('id');

        if ($existingMediaId !== null) {
            return $existingMediaId;
        }

        $media = Media::query()->create([
            'caption' => 'bulk-injection_'.now()->format('Ymd_His'),
            'file_name' => $permanentPath,
            'file_type' => $mimeType,
            'file_url' => $fileUrl,
            'file_size' => $r2->size($permanentPath),
        ]);

        return $media->id;
    }

    public function resolveStoragePath(Media $media): string
    {
        /** @var FilesystemAdapter $r2 */
        $r2 = Storage::disk('r2');

        $candidates = [];

        if (filled($media->file_name)) {
            $candidates[] = $media->file_name;
        }

        $r2BaseUrl = rtrim((string) config('filesystems.disks.r2.url'), '/');

        if (filled($r2BaseUrl) && str_starts_with($media->file_url, $r2BaseUrl)) {
            $candidates[] = ltrim(substr($media->file_url, strlen($r2BaseUrl)), '/');
        }

        $urlPath = parse_url($media->file_url, PHP_URL_PATH);

        if (is_string($urlPath) && $urlPath !== '') {
            $candidates[] = ltrim($urlPath, '/');

            $bucket = (string) config('filesystems.disks.r2.bucket');

            if ($bucket !== '' && str_starts_with(ltrim($urlPath, '/'), $bucket.'/')) {
                $candidates[] = substr(ltrim($urlPath, '/'), strlen($bucket) + 1);
            }
        }

        $candidates[] = 'imports/'.basename($media->file_name);

        foreach (array_unique(array_filter($candidates)) as $candidate) {
            if ($r2->exists($candidate)) {
                return $candidate;
            }
        }

        throw new RuntimeException('File spreadsheet tidak ditemukan di storage.');
    }

    public function downloadToTempFile(Media $media): string
    {
        /** @var FilesystemAdapter $r2 */
        $r2 = Storage::disk('r2');
        $storagePath = $this->resolveStoragePath($media);

        if (! $r2->exists($storagePath)) {
            throw new RuntimeException('File spreadsheet tidak ditemukan di storage.');
        }

        $tempDirectory = storage_path('app/temp/bulk-imports');

        if (! File::isDirectory($tempDirectory)) {
            File::makeDirectory($tempDirectory, 0755, true);
        }

        $tempPath = $tempDirectory.'/'.Str::uuid().'_'.basename($media->file_name);

        File::put($tempPath, $r2->get($storagePath));

        return $tempPath;
    }

    public function deleteTempFile(string $tempPath): void
    {
        if (File::exists($tempPath)) {
            File::delete($tempPath);
        }
    }

    private function normalizeUploadedPath(mixed $uploadedPath): ?string
    {
        if (is_string($uploadedPath) && filled($uploadedPath)) {
            return str_starts_with($uploadedPath, 'temp/')
                ? $uploadedPath
                : 'temp/'.ltrim($uploadedPath, '/');
        }

        if (is_array($uploadedPath)) {
            foreach ($uploadedPath as $path) {
                if (is_string($path) && filled($path)) {
                    return $this->normalizeUploadedPath($path);
                }
            }
        }

        return null;
    }

    private function assertAllowedSpreadsheet(string $path, FilesystemAdapter $disk): void
    {
        if (! $disk->exists($path)) {
            throw new InvalidArgumentException('File spreadsheet tidak ditemukan di staging.');
        }

        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        if (! in_array($extension, self::ALLOWED_EXTENSIONS, true)) {
            throw new InvalidArgumentException('Format file tidak didukung. Gunakan .xlsx, .xls, atau .csv.');
        }

        $mimeType = $disk->mimeType($path) ?? 'application/octet-stream';

        if (! in_array($mimeType, self::ALLOWED_MIME_TYPES, true)) {
            throw new InvalidArgumentException('Tipe MIME file tidak didukung.');
        }

        $maxBytes = 5 * 1024 * 1024;

        if ($disk->size($path) > $maxBytes) {
            throw new InvalidArgumentException('Ukuran file maksimal 5MB.');
        }
    }

    private function mimeTypeForPath(string $path): string
    {
        return match (strtolower(pathinfo($path, PATHINFO_EXTENSION))) {
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'xls' => 'application/vnd.ms-excel',
            'csv' => 'text/csv',
            default => 'application/octet-stream',
        };
    }
}
