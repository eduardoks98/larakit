<?php

namespace Eduardoks98\Permissions\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Cache;

class Profile extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'is_admin',
    ];

    protected $casts = [
        'is_admin' => 'boolean',
    ];

    protected $appends = [
        'permissions_count',
    ];

    /**
     * Get the table associated with the model.
     */
    public function getTable(): string
    {
        return config('permissions.tables.profiles', 'profiles');
    }

    /**
     * Get the users for the profile.
     */
    public function users(): HasMany
    {
        $userModel = config('permissions.user_model', 'App\\Models\\User');
        $foreignKey = config('permissions.profile_foreign_key', 'profile_id');

        return $this->hasMany($userModel, $foreignKey);
    }

    /**
     * Get the permissions for the profile.
     */
    public function permissions(): BelongsToMany
    {
        $pivotTable = config('permissions.tables.pivot', 'profile_permissions');

        return $this->belongsToMany(Permission::class, $pivotTable)
            ->withTimestamps();
    }

    /**
     * Check if the profile has a specific permission.
     */
    public function hasPermission(string $permission): bool
    {
        if ($this->is_admin && config('permissions.super_admin_bypass', true)) {
            return true;
        }

        $cacheKey = $this->getPermissionCacheKey($permission);

        if (config('permissions.cache.enabled', true)) {
            return Cache::remember($cacheKey, config('permissions.cache.ttl', 3600), function () use ($permission) {
                return $this->permissions()->where('name', $permission)->exists();
            });
        }

        return $this->permissions()->where('name', $permission)->exists();
    }

    /**
     * Check if the profile has all specified permissions.
     */
    public function hasPermissions(array $permissions): bool
    {
        if ($this->is_admin && config('permissions.super_admin_bypass', true)) {
            return true;
        }

        foreach ($permissions as $permission) {
            if (!$this->hasPermission($permission)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check if the profile has any of the specified permissions.
     */
    public function hasAnyPermission(array $permissions): bool
    {
        if ($this->is_admin && config('permissions.super_admin_bypass', true)) {
            return true;
        }

        foreach ($permissions as $permission) {
            if ($this->hasPermission($permission)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get all permission names for the profile.
     */
    public function getPermissions(): array
    {
        $cacheKey = config('permissions.cache.prefix', 'permissions_') . "profile_{$this->id}_all";

        if (config('permissions.cache.enabled', true)) {
            return Cache::remember($cacheKey, config('permissions.cache.ttl', 3600), function () {
                return $this->permissions()->pluck('name')->toArray();
            });
        }

        return $this->permissions()->pluck('name')->toArray();
    }

    /**
     * Sync permissions for the profile.
     */
    public function syncPermissions(array $permissionNames): void
    {
        $permissionIds = Permission::whereIn('name', $permissionNames)->pluck('id')->toArray();
        $this->permissions()->sync($permissionIds);
        $this->clearPermissionCache();
    }

    /**
     * Grant a permission to the profile.
     */
    public function grantPermission(string $permissionName): void
    {
        $permission = Permission::where('name', $permissionName)->first();

        if ($permission && !$this->permissions()->where('permission_id', $permission->id)->exists()) {
            $this->permissions()->attach($permission->id);
            $this->clearPermissionCache();
        }
    }

    /**
     * Revoke a permission from the profile.
     */
    public function revokePermission(string $permissionName): void
    {
        $permission = Permission::where('name', $permissionName)->first();

        if ($permission) {
            $this->permissions()->detach($permission->id);
            $this->clearPermissionCache();
        }
    }

    /**
     * Get the permissions count attribute.
     */
    public function getPermissionsCountAttribute(): int
    {
        return $this->permissions()->count();
    }

    /**
     * Get the default profile.
     */
    public static function getDefaultProfile(): ?self
    {
        $defaultName = config('permissions.default_profile', 'Usuario');

        return self::where('name', $defaultName)->first();
    }

    /**
     * Get the admin profile.
     */
    public static function getAdminProfile(): ?self
    {
        return self::where('is_admin', true)->first();
    }

    /**
     * Get cache key for a specific permission.
     */
    protected function getPermissionCacheKey(string $permission): string
    {
        return config('permissions.cache.prefix', 'permissions_') . "profile_{$this->id}_{$permission}";
    }

    /**
     * Clear the permission cache for this profile.
     */
    public function clearPermissionCache(): void
    {
        if (config('permissions.cache.enabled', true)) {
            $prefix = config('permissions.cache.prefix', 'permissions_');
            Cache::forget("{$prefix}profile_{$this->id}_all");

            foreach ($this->permissions as $permission) {
                Cache::forget($this->getPermissionCacheKey($permission->name));
            }
        }
    }
}
