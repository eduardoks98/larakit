<?php

use Eduardoks98\Permissions\Models\Profile;
use Eduardoks98\Permissions\Services\PermissionService;

if (!function_exists('has_permission')) {
    /**
     * Check if the current user has a specific permission.
     */
    function has_permission(string $permission): bool
    {
        $user = auth()->user();

        if (!$user) {
            return false;
        }

        if (method_exists($user, 'hasPermission')) {
            return $user->hasPermission($permission);
        }

        return false;
    }
}

if (!function_exists('has_any_permission')) {
    /**
     * Check if the current user has any of the specified permissions.
     */
    function has_any_permission(array $permissions): bool
    {
        $user = auth()->user();

        if (!$user) {
            return false;
        }

        if (method_exists($user, 'hasAnyPermission')) {
            return $user->hasAnyPermission($permissions);
        }

        return false;
    }
}

if (!function_exists('has_all_permissions')) {
    /**
     * Check if the current user has all of the specified permissions.
     */
    function has_all_permissions(array $permissions): bool
    {
        $user = auth()->user();

        if (!$user) {
            return false;
        }

        if (method_exists($user, 'hasPermissions')) {
            return $user->hasPermissions($permissions);
        }

        return false;
    }
}

if (!function_exists('is_admin')) {
    /**
     * Check if the current user is an administrator.
     */
    function is_admin(): bool
    {
        $user = auth()->user();

        if (!$user) {
            return false;
        }

        if (method_exists($user, 'isAdmin')) {
            return $user->isAdmin();
        }

        return false;
    }
}

if (!function_exists('current_profile')) {
    /**
     * Get the current user's profile.
     */
    function current_profile(): ?Profile
    {
        $user = auth()->user();

        if (!$user) {
            return null;
        }

        if (method_exists($user, 'profile') && $user->relationLoaded('profile')) {
            return $user->profile;
        }

        if (isset($user->profile)) {
            return $user->profile;
        }

        return null;
    }
}

if (!function_exists('user_permissions')) {
    /**
     * Get all permissions for the current user.
     */
    function user_permissions(): array
    {
        $user = auth()->user();

        if (!$user) {
            return [];
        }

        if (method_exists($user, 'getPermissions')) {
            return $user->getPermissions();
        }

        return [];
    }
}

if (!function_exists('permissions')) {
    /**
     * Get the permissions service instance.
     */
    function permissions(): PermissionService
    {
        return app(PermissionService::class);
    }
}
