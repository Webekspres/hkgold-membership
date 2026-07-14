<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TransactionType extends Model
{
    protected $table = 'transaction_types';

    protected $fillable = [
        'type_key',
        'display_name',
    ];

    public function conversionRules(): HasMany
    {
        return $this->hasMany(ConversionRule::class, 'transaction_type_id');
    }
}
