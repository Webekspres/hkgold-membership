<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\PostalCodeFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PostalCode extends Model
{
    /** @use HasFactory<PostalCodeFactory> */
    use HasFactory, HasUuids;

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $table = 'postal_codes';

    protected $fillable = [
        'code',
    ];

    public function addresses(): HasMany
    {
        return $this->hasMany(Address::class);
    }
}
