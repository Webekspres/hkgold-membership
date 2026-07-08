<?php

declare(strict_types=1);

namespace App\Filament\Resources\Rewards\Pages;

use App\Enums\ActivityLogAction;
use App\Filament\Resources\Rewards\RewardResource;
use App\Filament\Resources\Rewards\Support\RewardFormSupport;
use App\Services\ActivityLog\ActivityLogger;
use App\Support\ActivityLog\ActivityLogSanitizer;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EditReward extends EditRecord
{
    protected static string $resource = RewardResource::class;

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
        $this->record->loadMissing(['rewardImages.media']);

        return [
            ...$data,
            'reward_images' => $this->record->rewardImages
                ->map(fn ($rewardImage) => $rewardImage->media !== null
                    ? ['image' => RewardFormSupport::mediaToUploadPath($rewardImage->media)]
                    : null)
                ->filter()
                ->values()
                ->all(),
        ];
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $imagePaths = self::normalizeImagePaths($data['reward_images'] ?? []);
        $before = ActivityLogSanitizer::extract($record);

        $updatedRecord = DB::transaction(function () use ($record, $data, $imagePaths): Model {
            $record->update([
                'category_id' => $data['category_id'],
                'name' => $data['name'],
                'sku' => strtoupper((string) $data['sku']),
                'description' => RewardFormSupport::normalizeDescription($data['description'] ?? null),
                'points_required' => $data['points_required'],
                'is_active' => $data['is_active'] ?? true,
                'start_at' => $data['start_at'],
                'end_at' => $data['end_at'],
            ]);

            RewardFormSupport::syncImages($record, $imagePaths);

            return $record->refresh();
        });

        app(ActivityLogger::class)->log(
            action: ActivityLogAction::RewardUpdated,
            description: 'Memperbarui data reward',
            auditable: $updatedRecord,
            ipAddress: (string) request()->ip(),
            before: $before,
            after: ActivityLogSanitizer::extract($updatedRecord),
            actor: Auth::user(),
        );

        return $updatedRecord;
    }

    /**
     * Extract image paths from Repeater state: [uuid => ['image' => 'path'], ...]
     *
     * @param  array<mixed, mixed>  $items
     * @return array<int, string>
     */
    private static function normalizeImagePaths(array $items): array
    {
        return array_values(array_filter(
            array_map(fn (mixed $item): mixed => is_array($item) ? ($item['image'] ?? null) : null, $items),
            fn (mixed $path): bool => is_string($path) && filled($path),
        ));
    }
}
