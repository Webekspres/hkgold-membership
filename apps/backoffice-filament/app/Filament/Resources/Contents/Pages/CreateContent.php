<?php

declare(strict_types=1);

namespace App\Filament\Resources\Contents\Pages;

use App\Enums\ContentStatus;
use App\Enums\ContentType;
use App\Filament\Resources\Contents\ContentResource;
use App\Filament\Resources\Contents\Support\ContentFormSupport;
use App\Models\Content;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CreateContent extends CreateRecord
{
    protected static string $resource = ContentResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $state = ContentFormSupport::formState($this->form);

        return $this->persistDraft($data, $state['cover_images'] ?? [], $data['status'] ?? ContentStatus::Draft->value);
    }

    public function autoSave(): void
    {
        $state = ContentFormSupport::formState($this->form);
        $title = isset($state['title']) ? trim((string) $state['title']) : '';

        if ($title === '') {
            return;
        }

        $content = $this->persistDraft(
            $state,
            $state['cover_images'] ?? [],
            ContentStatus::Draft->value,
        );

        $this->redirect(ContentResource::getUrl('edit', ['record' => $content]));
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  array<int, array<string, mixed>>  $coverImages
     */
    protected function persistDraft(array $data, array $coverImages, string $status): Content
    {
        $title = isset($data['title']) && filled($data['title']) ? (string) $data['title'] : 'Untitled Draft';

        return DB::transaction(function () use ($data, $coverImages, $status, $title): Content {
            $content = Content::query()->create([
                'type' => $data['type'] ?? ContentType::News->value,
                'title' => $title,
                'slug' => ContentFormSupport::generateSlug($title),
                'body_content' => ContentFormSupport::normalizeBodyContent($data['body_content'] ?? null),
                'event_date' => ($data['type'] ?? ContentType::News->value) === ContentType::Event->value
                    ? ($data['event_date'] ?? null)
                    : null,
                'status' => $status,
            ]);

            ContentFormSupport::syncCoverImages($content, $coverImages);

            return $content;
        });
    }
}
