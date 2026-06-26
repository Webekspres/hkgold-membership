<?php

declare(strict_types=1);

namespace App\Filament\Resources\Members\Support;

use App\Models\Address;
use App\Models\Media;
use App\Models\Member;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MemberFormSupport
{
    /**
     * @return array<string, mixed>
     */
    public static function formState(Schema $form): array
    {
        return collect($form->getRawState())->all();
    }

    public static function formatPhoneForDisplay(?string $phone): ?string
    {
        if (blank($phone)) {
            return null;
        }

        $digits = preg_replace('/\D/', '', $phone);

        if (str_starts_with($digits, '62')) {
            return substr($digits, 2);
        }

        if (str_starts_with($digits, '0')) {
            return substr($digits, 1);
        }

        return $digits;
    }

    public static function normalizePhone(?string $phone): string
    {
        $digits = preg_replace('/\D/', '', $phone ?? '');

        if (str_starts_with($digits, '62')) {
            return $digits;
        }

        if (str_starts_with($digits, '0')) {
            return '62'.substr($digits, 1);
        }

        return '62'.$digits;
    }

    public static function generateMemberNumber(): string
    {
        do {
            $code = 'HK'.chr(random_int(65, 90)).str_pad((string) random_int(0, 9_999_999), 7, '0', STR_PAD_LEFT);
        } while (Member::query()->where('member_number', $code)->exists());

        return $code;
    }

    public static function storeProfilePhoto(mixed $uploadedPath, string $userName): ?string
    {
        $path = is_array($uploadedPath) ? ($uploadedPath[array_key_first($uploadedPath)] ?? null) : $uploadedPath;

        if (blank($path)) {
            return null;
        }

        $disk = Storage::disk('public');

        if (! $disk->exists($path)) {
            return null;
        }

        $absolutePath = $disk->path($path);
        self::optimizeProfileImage($absolutePath);

        $media = Media::query()->create([
            'caption' => 'photo-profile_'.Str::slug($userName, '_'),
            'file_name' => basename($path),
            'file_type' => $disk->mimeType($path) ?? 'application/octet-stream',
            'file_url' => $disk->url($path),
            'file_size' => $disk->size($path),
        ]);

        return $media->id;
    }

    /**
     * @param  array<string, mixed>  $state
     */
    public static function syncAddress(array $state, ?Address $existing = null): ?string
    {
        $villageId = $state['village_id'] ?? null;
        $postalCodeId = $state['postal_code_id'] ?? null;
        $street = filled($state['street'] ?? null) ? (string) $state['street'] : null;

        if (blank($villageId) && blank($postalCodeId) && blank($street)) {
            if ($existing !== null) {
                $existing->delete();
            }

            return null;
        }

        $payload = [
            'village_id' => $villageId,
            'postal_code_id' => $postalCodeId,
            'street' => $street ?? '',
        ];

        if ($existing !== null) {
            $existing->update($payload);

            return $existing->id;
        }

        return Address::query()->create($payload)->id;
    }

    public static function optimizeProfileImage(string $absolutePath, int $maxSize = 720, int $jpegQuality = 82): void
    {
        if (! function_exists('imagecreatefromjpeg')) {
            return;
        }

        $imageInfo = @getimagesize($absolutePath);

        if ($imageInfo === false) {
            return;
        }

        [$width, $height, $type] = $imageInfo;

        $source = match ($type) {
            IMAGETYPE_JPEG => @imagecreatefromjpeg($absolutePath),
            IMAGETYPE_PNG => @imagecreatefrompng($absolutePath),
            IMAGETYPE_WEBP => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($absolutePath) : null,
            default => null,
        };

        if (! $source instanceof \GdImage) {
            return;
        }

        $scale = min($maxSize / $width, $maxSize / $height, 1);
        $targetWidth = max(1, (int) round($width * $scale));
        $targetHeight = max(1, (int) round($height * $scale));

        $canvas = imagecreatetruecolor($targetWidth, $targetHeight);

        if ($canvas === false) {
            imagedestroy($source);

            return;
        }

        imagecopyresampled($canvas, $source, 0, 0, 0, 0, $targetWidth, $targetHeight, $width, $height);

        match ($type) {
            IMAGETYPE_JPEG => imagejpeg($canvas, $absolutePath, $jpegQuality),
            IMAGETYPE_PNG => imagepng($canvas, $absolutePath, 8),
            IMAGETYPE_WEBP => imagewebp($canvas, $absolutePath, $jpegQuality),
            default => null,
        };

        imagedestroy($source);
        imagedestroy($canvas);
    }
}
