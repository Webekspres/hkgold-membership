<?php

declare(strict_types=1);

namespace App\Filament\Resources\Contents\Pages;

use App\Enums\ActivityLogAction;
use App\Enums\ContentStatus;
use App\Enums\ContentType;
use App\Filament\Resources\Contents\ContentResource;
use App\Filament\Resources\Contents\Support\ContentFormSupport;
use App\Services\ActivityLog\ActivityLogger;
use App\Support\ActivityLog\ActivityLogSanitizer;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EditContent extends EditRecord
{
    protected static string $resource = ContentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        $this->record->loadMissing(['contentCoverImages.media']);

        return [
            ...$data,
            'cover_images' => $this->record->contentCoverImages
                ->map(fn ($coverImage) => $coverImage->media !== null
                    ? ContentFormSupport::mediaToUploadPath($coverImage->media)
                    : null)
                ->filter()
                ->values()
                ->all(),
        ];
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $state = ContentFormSupport::formState($this->form);
        $before = ActivityLogSanitizer::extract($record);

        $updatedRecord = $this->persistRecord(
            $record,
            $data,
            self::normalizeCoverImagePaths($state['cover_images'] ?? []),
            $data['status'] ?? ContentStatus::Draft->value,
        );

        app(ActivityLogger::class)->log(
            action: ActivityLogAction::ContentUpdated,
            description: 'Memperbarui konten',
            auditable: $updatedRecord,
            ipAddress: (string) request()->ip(),
            before: $before,
            after: ActivityLogSanitizer::extract($updatedRecord),
            actor: Auth::user(),
        );

        return $updatedRecord;
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  array<int, string>  $coverImagePaths
     */
    protected function persistRecord(Model $record, array $data, array $coverImagePaths, string $status): Model
    {
        return DB::transaction(function () use ($record, $data, $coverImagePaths, $status): Model {
            $title = isset($data['title']) && filled($data['title']) ? (string) $data['title'] : 'Untitled Draft';

            $record->update([
                'type' => $data['type'] ?? ContentType::News->value,
                'title' => $title,
                'slug' => ContentFormSupport::generateSlug($title, $record->id),
                'body_content' => ContentFormSupport::normalizeBodyContent($data['body_content'] ?? null),
                'event_date' => ($data['type'] ?? ContentType::News->value) === ContentType::Event->value
                    ? ($data['event_date'] ?? null)
                    : null,
                'status' => $status,
            ]);

            ContentFormSupport::syncCoverImages($record, $coverImagePaths);

            return $record->refresh();
        });
    }

    /**
     * @param  array<int, mixed>  $paths
     * @return array<int, string>
     */
    private static function normalizeCoverImagePaths(array $paths): array
    {
        return array_values(array_filter($paths, fn (mixed $path): bool => is_string($path) && filled($path)));
    }
}
