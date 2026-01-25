<?php

namespace Eduardoks98\Permissions\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Collection;

class Permission extends Model
{
    protected $fillable = [
        'name',
        'description',
    ];

    protected $appends = [
        'module',
    ];

    /**
     * Get the table associated with the model.
     */
    public function getTable(): string
    {
        return config('permissions.tables.permissions', 'permissions');
    }

    /**
     * Get the profiles for the permission.
     */
    public function profiles(): BelongsToMany
    {
        $pivotTable = config('permissions.tables.pivot', 'profile_permissions');
        $profilesTable = config('permissions.tables.profiles', 'profiles');

        return $this->belongsToMany(Profile::class, $pivotTable)
            ->withTimestamps();
    }

    /**
     * Get the module from the permission name.
     * Extracts module from format: admin:module:action
     */
    public function getModuleAttribute(): string
    {
        $parts = explode(':', $this->name);

        if (count($parts) >= 2) {
            return strtoupper($parts[1]);
        }

        return strtoupper($parts[0] ?? 'GENERAL');
    }

    /**
     * Get the action from the permission name.
     * Extracts action from format: admin:module:action
     */
    public function getActionAttribute(): string
    {
        $parts = explode(':', $this->name);

        return $parts[2] ?? $parts[1] ?? $parts[0] ?? '';
    }

    /**
     * Get the prefix from the permission name.
     * Extracts prefix from format: admin:module:action
     */
    public function getPrefixAttribute(): string
    {
        $parts = explode(':', $this->name);

        return $parts[0] ?? '';
    }

    /**
     * Get permissions by module.
     */
    public static function getByModule(string $module): Collection
    {
        return self::all()->filter(function ($permission) use ($module) {
            return strtoupper($permission->module) === strtoupper($module);
        });
    }

    /**
     * Get all permissions grouped by module.
     */
    public static function getAllGroupedByModule(): array
    {
        return self::all()
            ->groupBy('module')
            ->map(function ($permissions) {
                return $permissions->map(function ($permission) {
                    return [
                        'id' => $permission->id,
                        'name' => $permission->name,
                        'description' => $permission->description,
                        'action' => $permission->action,
                    ];
                })->toArray();
            })
            ->toArray();
    }

    /**
     * Get all unique modules.
     */
    public static function getModules(): array
    {
        return self::all()
            ->pluck('module')
            ->unique()
            ->sort()
            ->values()
            ->toArray();
    }

    /**
     * Create or update a permission.
     */
    public static function createOrUpdate(string $name, ?string $description = null): self
    {
        return self::updateOrCreate(
            ['name' => $name],
            ['description' => $description]
        );
    }

    /**
     * Find permission by name.
     */
    public static function findByName(string $name): ?self
    {
        return self::where('name', $name)->first();
    }
}
