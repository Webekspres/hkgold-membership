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

    protected $table = 'phone_approvals';

    protected $fillable = [
        'member_id',
        'old_phone',
        'new_phone',
        'status',
        'approved_by',
    ];

    protected function casts(): array
    {
        return [
            'status' => ApprovalStatus::class,
        ];
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'approved_by');
    }
}
