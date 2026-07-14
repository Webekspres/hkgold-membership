<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ApprovalStatus;
use Database\Factories\PhoneApprovalFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PhoneApproval extends Model
{
    /** @use HasFactory<PhoneApprovalFactory> */
    use HasFactory, HasUuids;

    protected $table = 'change_phone_approvals';

    protected $fillable = [
        'member_id',
        'requested_by_id',
        'approved_by_id',
        'old_phone_number',
        'new_phone_number',
        'status',
        'reason',
        'action_notes',
        'processed_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => ApprovalStatus::class,
            'processed_at' => 'datetime',
        ];
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'requested_by_id');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'approved_by_id');
    }
}
