<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\VillageFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Village extends Model
{
    /** @use HasFactory<VillageFactory> */
    use HasFactory, HasUuids;

    public $timestamps = false;

    protected $table = 'villages';

    protected $fillable = [
        'district_id',
        'name',
    ];

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(Address::class);
    }
}
