<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\HasAuditableActivityLogs;
use Database\Factories\BranchFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Branch extends Model
{
    /** @use HasFactory<BranchFactory> */
    use HasAuditableActivityLogs, HasFactory;

    protected $table = 'branches';

    protected $fillable = [
        'branch_code',
        'name',
        'address',
        'address_id',
        'phone',
        'location_url',
        'is_online_warehouse',
    ];

    protected function casts(): array
    {
        return [
            'is_online_warehouse' => 'boolean',
        ];
    }

    public function normalizedAddress(): BelongsTo
    {
        return $this->belongsTo(Address::class, 'address_id');
    }

    public function staffs(): HasMany
    {
        return $this->hasMany(Staff::class);
    }

    public function registeredMembers(): HasMany
    {
        return $this->hasMany(Member::class, 'registered_at_branch_id');
    }

    public function rewardStocks(): HasMany
    {
        return $this->hasMany(BranchRewardStock::class);
    }

    public function pointMutations(): HasMany
    {
        return $this->hasMany(PointMutation::class);
    }

    public function redeemInvoices(): HasMany
    {
        return $this->hasMany(RedeemInvoice::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(BranchImage::class)->orderBy('sort_order');
    }
}
