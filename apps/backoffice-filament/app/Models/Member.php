<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\TierStatus;
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
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'members';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'address_id',
        'member_code',
        'dob',
        'total_points',
        'tier',
        'phone_change_pending',
    ];

    protected function casts(): array
    {
        return [
            'dob' => 'date',
            'total_points' => 'decimal:2',
            'tier' => TierStatus::class,
            'phone_change_pending' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id');
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

    public function phoneApprovals(): HasMany
    {
        return $this->hasMany(PhoneApproval::class);
    }

    public function inactivityLogs(): HasMany
    {
        return $this->hasMany(MemberAnomaly::class);
    }

    public function fraudSuspectsAsOne(): HasMany
    {
        return $this->hasMany(FraudSuspect::class, 'member_1_id');
    }

    public function fraudSuspectsAsTwo(): HasMany
    {
        return $this->hasMany(FraudSuspect::class, 'member_2_id');
    }

    public function annualArchives(): HasMany
    {
        return $this->hasMany(PointAnnualArchive::class);
    }
}
