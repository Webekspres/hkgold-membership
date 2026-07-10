<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Role;
use BezhanSalleh\FilamentShield\Support\Utils;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Models\Role as SpatieRole;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, HasUuids, Notifiable;

    protected $table = 'users';

    protected $fillable = [
        'email',
        'phone',
        'password',
        'name',
        'role',
        'profile_photo_id',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'role' => Role::class,
            'is_active' => 'boolean',
            'password' => 'hashed',
        ];
    }

    public function staff(): HasOne
    {
        return $this->hasOne(Staff::class, 'id');
    }

    public function member(): HasOne
    {
        return $this->hasOne(Member::class, 'id');
    }

    public function profilePhoto(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'profile_photo_id');
    }

    public function canAccessPanel(Panel $panel): bool
    {
        if (! $this->is_active || $this->role === Role::Customer) {
            return false;
        }

        if (! SpatieRole::query()->exists()) {
            return true;
        }

        return $this->hasRole(Utils::getSuperAdminName())
            || $this->hasRole(Utils::getPanelUserRoleName());
    }
}
