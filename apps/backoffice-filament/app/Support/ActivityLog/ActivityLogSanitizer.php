<?php

declare(strict_types=1);

namespace App\Support\ActivityLog;

use App\Models\Branch;
use App\Models\Content;
use App\Models\Member;
use App\Models\Reward;
use App\Models\Staff;
use App\Models\TierMember;
use Illuminate\Database\Eloquent\Model;

class ActivityLogSanitizer
{
    /**
     * @var array<class-string<Model>, array<int, string>>
     */
    private const WHITELIST = [
        Member::class => [
            'member_number',
            'phone_number',
            'current_tier',
            'is_suspended',
            'registered_at_branch_id',
            'address_id',
        ],
        Staff::class => [
            'branch_id',
            'employee_code',
        ],
        Reward::class => [
            'category_id',
            'name',
            'sku',
            'description',
            'points_required',
            'is_active',
            'start_at',
            'end_at',
        ],
        Branch::class => [
            'branch_code',
            'name',
            'address',
            'phone',
            'location_url',
            'latitude',
            'longitude',
            'is_online_warehouse',
            'address_id',
        ],
        Content::class => [
            'type',
            'title',
            'slug',
            'event_date',
            'status',
            'is_staged',
        ],
        TierMember::class => [
            'tier_code',
            'min_points',
            'max_points',
        ],
    ];

    /**
     * @return array<string, mixed>
     */
    public static function extract(Model $model): array
    {
        return self::extractArray($model->attributesToArray(), $model::class);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public static function extractArray(array $data, string $class): array
    {
        $keys = self::WHITELIST[$class] ?? [];

        if ($keys === []) {
            return [];
        }

        $filtered = [];

        foreach ($keys as $key) {
            if (array_key_exists($key, $data)) {
                $filtered[$key] = $data[$key];
            }
        }

        return $filtered;
    }
}
