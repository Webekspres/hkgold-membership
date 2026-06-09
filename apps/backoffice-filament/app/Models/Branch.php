<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\BranchFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Branch extends Model
{
    /** @use HasFactory<BranchFactory> */
    use HasFactory, HasUuids;

    protected $table = 'branches';

    protected $fillable = [
        'address_id',
        'code',
        'name',
        'phone',
        'latitude',
        'longitude',
        'is_active',
        'open_time',
        'close_time',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
            'is_active' => 'boolean',
        ];
    }

    public function address(): BelongsTo
    {
        return $this->belongsTo(Address::class);
    }

    public function staffs(): HasMany
    {
        return $this->hasMany(Staff::class);
    }

    public function rewardStocks(): HasMany
    {
        return $this->hasMany(BranchRewardStock::class);
    }

    public function pointBatches(): HasMany
    {
        return $this->hasMany(PointInjectionBatch::class);
    }

    public function pointMutations(): HasMany
    {
        return $this->hasMany(PointMutation::class);
    }

    public function redeemInvoices(): HasMany
    {
        return $this->hasMany(RedeemInvoice::class);
    }
}
