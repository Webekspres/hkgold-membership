<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConversionRule extends Model
{
    use HasUuids;

    protected $table = 'conversion_rules';

    protected $fillable = [
        'transaction_type_id',
        'tier_member_id',
        'conversion_nominal',
    ];

    protected function casts(): array
    {
        return [
            'conversion_nominal'  => 'decimal:2',
            'transaction_type_id' => 'integer',
            'tier_member_id'      => 'integer',
        ];
    }

    public function transactionType(): BelongsTo
    {
        return $this->belongsTo(TransactionType::class, 'transaction_type_id');
    }

    public function tierMember(): BelongsTo
    {
        return $this->belongsTo(TierMember::class, 'tier_member_id');
    }
}
