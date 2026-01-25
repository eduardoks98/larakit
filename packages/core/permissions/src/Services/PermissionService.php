<?php

namespace Eduardoks98\Permissions\Services;

use Eduardoks98\Permissions\Contracts\PermissionEnum;
use Eduardoks98\Permissions\Models\Permission;
use Eduardoks98\Permissions\Models\Profile;
use Illuminate\Support\Facades\Cache;

class PermissionService
{
    /**
     * Get the configured permission enum class.
     */
    public function getPermissionEnumClass(): ?string
    {
        return config('permissions.permission_enum');
    }

    /**
     * Check if a permission enum is configured.
     */
    public function hasPermissionEnum(): bool
    {
        $enumClass = $this->getPermissionEnumClass();

        return $enumClass && enum_exists($enumClass);
    }

    /**
     * Get all permissions from the enum.
     */
    public function getEnumPermissions(): array
    {
        $enumClass = $this->getPermissionEnumClass();

        if (!$enumClass || !enum_exists($enumClass)) {
            return [];
        }

        return $enumClass::cases();
    }

    /**
     * Get permissions grouped by module from the enum.
     */
    public function getEnumPermissionsGrouped(): array
    {
        $enumClass = $this->getPermissionEnumClass();

        if (!$enumClass || !enum_exists($enumClass)) {
            return [];
        }

        if (!is_subclass_of($enumClass, PermissionEnum::class)) {
            return [];
        }

        return $enumClass::groupedByModule();
    }

    /**
     * Sync permissions from enum to database.
     */
    public function syncPermissionsFromEnum(): array
    {
        $enumClass = $this->getPermissionEnumClass();

        if (!$enumClass || !enum_exists($enumClass)) {
            return [
                'created' => 0,
                'updated' => 0,
                'errors' => ['No permission enum configured'],
            ];
        }

        $created = 0;
        $updated = 0;
        $errors = [];

        foreach ($enumClass::cases() as $permission) {
            try {
                $name = $permission->value;
                $description = method_exists($permission, 'label')
                    ? $permission->label()
                    : null;

                $existing = Permission::where('name', $name)->first();

                if ($existing) {
                    if ($existing->description !== $description) {
                        $existing->update(['description' => $description]);
                        $updated++;
                    }
                } else {
                    Permission::create([
                        'name' => $name,
                        'description' => $description,
                    ]);
                    $created++;
                }
            } catch (\Exception $e) {
                $errors[] = "Failed to sync permission {$permission->value}: {$e->getMessage()}";
            }
        }

        $this->clearCache();

        return [
            'created' => $created,
            'updated' => $updated,
            'errors' => $errors,
        ];
    }

    /**
     * Get all permissions from database grouped by module.
     */
    public function getPermissionsGroupedByModule(): array
    {
        return Permission::getAllGroupedByModule();
    }

    /**
     * Get permission options for Filament forms.
     */
    public function getPermissionOptionsForFilament(): array
    {
        $options = [];
        $permissions = Permission::all();

        foreach ($permissions as $permission) {
            $options[$permission->id] = $permission->description ?? $permission->name;
        }

        return $options;
    }

    /**
     * Get permission options grouped by module for Filament forms.
     */
    public function getPermissionOptionsGroupedForFilament(): array
    {
        $grouped = [];
        $permissions = Permission::all();

        foreach ($permissions as $permission) {
            $module = $permission->module;
            $moduleLabel = $this->getModuleLabel($module);

            if (!isset($grouped[$moduleLabel])) {
                $grouped[$moduleLabel] = [];
            }

            $grouped[$moduleLabel][$permission->id] = $permission->description ?? $permission->name;
        }

        ksort($grouped);

        return $grouped;
    }

    /**
     * Get the label for a module.
     */
    public function getModuleLabel(string $module): string
    {
        $enumClass = $this->getPermissionEnumClass();

        if ($enumClass && enum_exists($enumClass) && method_exists($enumClass, 'moduleLabel')) {
            return $enumClass::moduleLabel($module);
        }

        return ucfirst(strtolower($module));
    }

    /**
     * Check if a user has a permission.
     */
    public function userHasPermission($user, string $permission): bool
    {
        if (!$user) {
            return false;
        }

        if (method_exists($user, 'hasPermission')) {
            return $user->hasPermission($permission);
        }

        return false;
    }

    /**
     * Get all profiles.
     */
    public function getAllProfiles(): array
    {
        return Profile::with('permissions')->get()->toArray();
    }

    /**
     * Get a profile by name.
     */
    public function getProfileByName(string $name): ?Profile
    {
        return Profile::where('name', $name)->first();
    }

    /**
     * Create a new profile.
     */
    public function createProfile(string $name, ?string $description = null, bool $isAdmin = false, array $permissionNames = []): Profile
    {
        $profile = Profile::create([
            'name' => $name,
            'description' => $description,
            'is_admin' => $isAdmin,
        ]);

        if (!empty($permissionNames)) {
            $profile->syncPermissions($permissionNames);
        }

        return $profile;
    }

    /**
     * Clear all permission caches.
     */
    public function clearCache(): void
    {
        if (config('permissions.cache.enabled', true)) {
            $prefix = config('permissions.cache.prefix', 'permissions_');

            // Clear profile-specific caches
            $profiles = Profile::all();
            foreach ($profiles as $profile) {
                $profile->clearPermissionCache();
            }
        }
    }

    /**
     * Seed default profiles.
     */
    public function seedDefaultProfiles(): array
    {
        $created = [];

        // Admin profile
        $admin = Profile::firstOrCreate(
            ['name' => 'Administrador'],
            [
                'description' => 'Acesso total ao sistema',
                'is_admin' => true,
            ]
        );

        if ($admin->wasRecentlyCreated) {
            $created[] = 'Administrador';
        }

        // Default user profile
        $defaultName = config('permissions.default_profile', 'Usuario');
        $user = Profile::firstOrCreate(
            ['name' => $defaultName],
            [
                'description' => 'Perfil padrão de usuário',
                'is_admin' => false,
            ]
        );

        if ($user->wasRecentlyCreated) {
            $created[] = $defaultName;
        }

        return $created;
    }
}
