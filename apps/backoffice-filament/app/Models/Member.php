<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\TierStatus;
use App\Models\Concerns\HasAuditableActivityLogs;
use Database\Factories\MemberFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Member extends Model
{
    /** @use HasFactory<MemberFactory> */
    use HasAuditableActivityLogs, HasFactory, HasUuids, SoftDeletes;

    protected $table = 'members';

    protected $fillable = [
        'user_id',
        'registered_at_branch_id',
        'address_id',
        'member_number',
        'phone_number',
        'current_tier',
        'point_balance',
        'highest_point',
        'last_activity_at',
        'is_suspended',
        'birth_date',
        'gender',
        'phone_changed_at',
    ];

    protected function casts(): array
    {
        return [
            'current_tier' => TierStatus::class,
            'point_balance' => 'integer',
            'highest_point' => 'integer',
            'last_activity_at' => 'datetime',
            'is_suspended' => 'boolean',
            'birth_date' => 'date',
            'phone_changed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function registeredBranch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'registered_at_branch_id');
    }

    public function address(): BelongsTo
    {
        return $this->belongsTo(Address::class);
    }

    public function mutations(): HasMany
    {
        return $this->hasMany(PointMutation::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(RedeemInvoice::class);
    }

    public function redeemTokens(): HasMany
    {
        return $this->hasMany(RedeemToken::class);
    }

    public function phoneApprovals(): HasMany
    {
        return $this->hasMany(PhoneApproval::class);
    }

    public function inactivityLogs(): HasMany
    {
        return $this->hasMany(MemberAnomaly::class);
    }

    public function annualArchives(): HasMany
    {
        return $this->hasMany(PointAnnualArchive::class);
    }
}
