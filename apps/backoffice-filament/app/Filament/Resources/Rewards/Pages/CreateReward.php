<?php

declare(strict_types=1);

namespace App\Filament\Resources\Rewards\Pages;

use App\Enums\ActivityLogAction;
use App\Filament\Resources\Rewards\RewardResource;
use App\Filament\Resources\Rewards\Support\RewardFormSupport;
use App\Models\Reward;
use App\Services\ActivityLog\ActivityLogger;
use App\Support\ActivityLog\ActivityLogSanitizer;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CreateReward extends CreateRecord
{
    protected static string $resource = RewardResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $imagePaths = self::normalizeImagePaths($data['reward_images'] ?? []);

        $record = DB::transaction(function () use ($data, $imagePaths): Reward {
            $reward = Reward::query()->create([
                'category_id' => $data['category_id'],
                'name' => $data['name'],
                'sku' => strtoupper((string) $data['sku']),
                'description' => RewardFormSupport::normalizeDescription($data['description'] ?? null),
                'points_required' => $data['points_required'],
                'is_active' => $data['is_active'] ?? true,
                'start_at' => $data['start_at'],
                'end_at' => $data['end_at'],
            ]);

            RewardFormSupport::syncImages($reward, $imagePaths);

            return $reward;
        });

        app(ActivityLogger::class)->log(
            action: ActivityLogAction::RewardCreated,
            description: 'Membuat data reward baru',
            auditable: $record,
            ipAddress: (string) request()->ip(),
            after: ActivityLogSanitizer::extract($record),
            actor: Auth::user(),
        );

        return $record;
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
