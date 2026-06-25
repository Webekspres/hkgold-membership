<?php

declare(strict_types=1);

namespace App\Filament\Resources\Contents\Pages;

use App\Enums\ContentType;
use App\Filament\Resources\Contents\ContentResource;
use App\Filament\Resources\Contents\Support\ContentFormSupport;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
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
            'slug' => $this->record->slug,
            'cover_images' => $this->record->contentCoverImages
                ->map(fn ($coverImage): array => [
                    'media_id' => $coverImage->media_id,
                    'image' => $coverImage->media !== null
                        ? ContentFormSupport::mediaToUploadPath($coverImage->media)
                        : null,
                ])
                ->values()
                ->all(),
        ];
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $state = ContentFormSupport::formState($this->form);

        return DB::transaction(function () use ($record, $data, $state): Model {
            $record->update([
                'type' => $data['type'],
                'title' => $data['title'],
                'body_content' => $data['body_content'],
                'event_date' => $data['type'] === ContentType::Event->value
                    ? ($data['event_date'] ?? null)
                    : null,
                'is_active' => $data['is_active'] ?? true,
            ]);

            ContentFormSupport::syncCoverImages($record, $state['cover_images'] ?? []);

            return $record->refresh();
        });
    }
}
