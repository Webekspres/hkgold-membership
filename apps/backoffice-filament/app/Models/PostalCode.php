<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\PostalCodeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PostalCode extends Model
{
    /** @use HasFactory<PostalCodeFactory> */
    use HasFactory;

    public $timestamps = false;

    protected $table = 'postal_codes';

    protected $fillable = [
        'city_id',
        'sub_district_id',
        'kodepos',
    ];

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class, 'city_id');
    }

    public function subDistrict(): BelongsTo
    {
        return $this->belongsTo(SubDistrict::class, 'sub_district_id');
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(Address::class);
    }
}
