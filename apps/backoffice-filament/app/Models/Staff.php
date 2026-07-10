<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\StaffFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Staff extends Model
{
    /** @use HasFactory<StaffFactory> */
    use HasFactory, HasUuids;

    protected $table = 'staffs';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'branch_id',
        'allowed_ip',
        'is_device_approved',
    ];

    protected function casts(): array
    {
        return [
            'is_device_approved' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function uploadedBatches(): HasMany
    {
        return $this->hasMany(PointInjectionBatch::class, 'uploaded_by_id');
    }

    public function confirmedInvoices(): HasMany
    {
        return $this->hasMany(RedeemInvoice::class, 'confirmed_by_id');
    }

    public function cancelledInvoices(): HasMany
    {
        return $this->hasMany(RedeemInvoice::class, 'cancelled_by_id');
    }

    public function approvedPhones(): HasMany
    {
        return $this->hasMany(PhoneApproval::class, 'approved_by');
    }
}
