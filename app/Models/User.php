<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['user_type_id', 'organization_id', 'name', 'email', 'phone', 'password', 'is_super_admin', 'status', 'created_by'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'is_super_admin' => 'boolean',
            'password' => 'hashed',
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class)
            ->withTimestamps();
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class)
            ->withTimestamps();
    }

    public function features(): BelongsToMany
    {
        return $this->belongsToMany(Feature::class)
            ->withTimestamps();
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(self::class, 'created_by');
    }

    public function createdUsers(): HasMany
    {
        return $this->hasMany(self::class, 'created_by');
    }

    public function openedReparationCases(): HasMany
    {
        return $this->hasMany(ReparationCase::class, 'opened_by_user_id');
    }

    public function assignedReparationCases(): HasMany
    {
        return $this->hasMany(ReparationCase::class, 'assigned_to_user_id');
    }

    public function bailiffReparationCases(): HasMany
    {
        return $this->hasMany(ReparationCase::class, 'bailiff_user_id');
    }

    public function lawyerReparationCases(): HasMany
    {
        return $this->hasMany(ReparationCase::class, 'lawyer_user_id');
    }

    public function assignedReparationCaseSteps(): HasMany
    {
        return $this->hasMany(ReparationCaseStep::class, 'assigned_to_user_id');
    }

    public function permissionCodes(): Collection
    {
        $this->loadMissing(['permissions', 'roles.permissions']);

        return $this->permissions
            ->pluck('code')
            ->merge($this->roles->flatMap(fn ($role) => $role->permissions->pluck('code')))
            ->unique()
            ->values();
    }

    public function hasPermissionCode(string $permissionCode): bool
    {
        if ($this->is_super_admin) {
            return true;
        }

        return $this->permissionCodes()->contains($permissionCode);
    }
}
