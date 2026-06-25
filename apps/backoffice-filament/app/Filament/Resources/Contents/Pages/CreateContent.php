<?php

declare(strict_types=1);

namespace App\Filament\Resources\Contents\Pages;

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

        return DB::transaction(function () use ($data, $state): Content {
            $content = Content::query()->create([
                'type' => $data['type'],
                'title' => $data['title'],
                'slug' => ContentFormSupport::generateSlug($data['title']),
                'body_content' => $data['body_content'],
                'event_date' => $data['type'] === ContentType::Event->value
                    ? ($data['event_date'] ?? null)
                    : null,
                'is_active' => $data['is_active'] ?? true,
            ]);

            ContentFormSupport::syncCoverImages($content, $state['cover_images'] ?? []);

            return $content;
        });
    }
}
