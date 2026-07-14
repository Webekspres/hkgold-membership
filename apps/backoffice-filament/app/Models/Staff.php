<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\HasAuditableActivityLogs;
use Database\Factories\StaffFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Staff extends Model
{
    /** @use HasFactory<StaffFactory> */
    use HasAuditableActivityLogs, HasFactory, SoftDeletes;

    protected $table = 'staffs';

    protected $fillable = [
        'user_id',
        'branch_id',
        'employee_code',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function uploadedBatches(): HasMany
    {
        return $this->hasMany(PointInjectionBatch::class, 'staff_id');
    }

    public function redeemInvoices(): HasMany
    {
        return $this->hasMany(RedeemInvoice::class, 'staff_id');
    }

    public function changePhoneRequests(): HasMany
    {
        return $this->hasMany(PhoneApproval::class, 'requested_by_id');
    }

    public function changePhoneApprovals(): HasMany
    {
        return $this->hasMany(PhoneApproval::class, 'approved_by_id');
    }
}
