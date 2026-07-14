<?php

declare(strict_types=1);

namespace App\Filament\Resources\Contents\Pages;

use App\Enums\ActivityLogAction;
use App\Enums\ContentStatus;
use App\Enums\ContentType;
use App\Filament\Resources\Contents\ContentResource;
use App\Filament\Resources\Contents\Support\ContentFormSupport;
use App\Models\Content;
use App\Services\ActivityLog\ActivityLogger;
use App\Support\ActivityLog\ActivityLogSanitizer;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Renderless;

class CreateContent extends CreateRecord
{
    private const DRAFT_TEXT_FIELDS = [
        'type',
        'status',
        'title',
        'body_content',
        'event_date',
    ];

    protected ?Content $createdContent = null;

    protected static string $resource = ContentResource::class;

    public function mount(): void
    {
        parent::mount();

        $this->js('window.dispatchEvent(new CustomEvent("content-draft-restore"))');
    }

    /**
     * @return array<string, mixed>
     */
    #[Renderless]
    public function getDraftSnapshot(): array
    {
        return Arr::only(ContentFormSupport::formState($this->form), self::DRAFT_TEXT_FIELDS);
    }

    /**
     * @param  array<string, mixed>  $state
     */
    public function restoreDraftSnapshot(array $state): void
    {
        $this->form->fill(Arr::only($state, self::DRAFT_TEXT_FIELDS));
    }

    protected function handleRecordCreation(array $data): Model
    {
        $state = ContentFormSupport::formState($this->form);

        $this->createdContent = $this->persistDraft(
            $data,
            self::normalizeCoverImagePaths($state['cover_images'] ?? []),
            $data['status'] ?? ContentStatus::Draft->value,
        );

        return $this->createdContent;
    }

    protected function afterCreate(): void
    {
        if ($this->createdContent !== null) {
            app(ActivityLogger::class)->log(
                action: ActivityLogAction::ContentCreated,
                description: 'Membuat konten baru',
                auditable: $this->createdContent,
                ipAddress: (string) request()->ip(),
                after: ActivityLogSanitizer::extract($this->createdContent),
                actor: Auth::user(),
            );
        }

        $this->js("localStorage.removeItem('hkgold-content-draft')");
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  array<int, string>  $coverImagePaths
     */
    protected function persistDraft(array $data, array $coverImagePaths, string $status): Content
    {
        $title = isset($data['title']) && filled($data['title']) ? (string) $data['title'] : 'Untitled Draft';

        return DB::transaction(function () use ($data, $coverImagePaths, $status, $title): Content {
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

            ContentFormSupport::syncCoverImages($content, $coverImagePaths);

            return $content;
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
