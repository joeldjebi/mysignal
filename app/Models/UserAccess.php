<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Collection;

class UserAccess extends Model
{
    protected $fillable = [
        'user_id',
        'organization_id',
        'portal',
        'status',
        'created_by',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'user_access_role')
            ->withTimestamps();
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'permission_user_access')
            ->withTimestamps();
    }

    public function hasScopedAuthorization(): bool
    {
        if (! $this->exists) {
            return false;
        }

        return $this->roles()->exists() || $this->permissions()->exists();
    }

    public function permissionCodes(): Collection
    {
        $this->loadMissing(['permissions', 'roles.permissions']);

        return $this->permissions
            ->pluck('code')
            ->merge($this->roles->flatMap(fn (Role $role) => $role->permissions->pluck('code')))
            ->unique()
            ->values();
    }
}
