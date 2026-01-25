<?php

namespace Eduardoks98\Permissions\Traits;

use Illuminate\Database\Eloquent\Model;

/**
 * Trait to add permission-based authorization to Filament Resources.
 *
 * Usage:
 * class UserResource extends Resource
 * {
 *     use HasResourcePermissions;
 *
 *     protected static function getViewPermission(): string
 *     {
 *         return 'admin:users:view';
 *     }
 *
 *     protected static function getCreatePermission(): string
 *     {
 *         return 'admin:users:create';
 *     }
 *
 *     protected static function getEditPermission(): string
 *     {
 *         return 'admin:users:edit';
 *     }
 *
 *     protected static function getDeletePermission(): string
 *     {
 *         return 'admin:users:delete';
 *     }
 * }
 */
trait HasResourcePermissions
{
    /**
     * Get the permission required to view any records.
     */
    abstract protected static function getViewPermission(): string;

    /**
     * Get the permission required to create records.
     */
    abstract protected static function getCreatePermission(): string;

    /**
     * Get the permission required to edit records.
     */
    abstract protected static function getEditPermission(): string;

    /**
     * Get the permission required to delete records.
     */
    abstract protected static function getDeletePermission(): string;

    /**
     * Check if the current user can view any records.
     */
    public static function canViewAny(): bool
    {
        $user = auth()->user();

        if (!$user) {
            return false;
        }

        if (method_exists($user, 'hasPermission')) {
            return $user->hasPermission(static::getViewPermission());
        }

        return false;
    }

    /**
     * Check if the current user can view a specific record.
     */
    public static function canView(Model $record): bool
    {
        return static::canViewAny();
    }

    /**
     * Check if the current user can create records.
     */
    public static function canCreate(): bool
    {
        $user = auth()->user();

        if (!$user) {
            return false;
        }

        if (method_exists($user, 'hasPermission')) {
            return $user->hasPermission(static::getCreatePermission());
        }

        return false;
    }

    /**
     * Check if the current user can edit a specific record.
     */
    public static function canEdit(Model $record): bool
    {
        $user = auth()->user();

        if (!$user) {
            return false;
        }

        if (method_exists($user, 'hasPermission')) {
            return $user->hasPermission(static::getEditPermission());
        }

        return false;
    }

    /**
     * Check if the current user can delete a specific record.
     */
    public static function canDelete(Model $record): bool
    {
        $user = auth()->user();

        if (!$user) {
            return false;
        }

        if (method_exists($user, 'hasPermission')) {
            return $user->hasPermission(static::getDeletePermission());
        }

        return false;
    }

    /**
     * Check if the current user can delete any records.
     */
    public static function canDeleteAny(): bool
    {
        $user = auth()->user();

        if (!$user) {
            return false;
        }

        if (method_exists($user, 'hasPermission')) {
            return $user->hasPermission(static::getDeletePermission());
        }

        return false;
    }

    /**
     * Check if the current user can force delete a specific record.
     */
    public static function canForceDelete(Model $record): bool
    {
        return static::canDelete($record);
    }

    /**
     * Check if the current user can force delete any records.
     */
    public static function canForceDeleteAny(): bool
    {
        return static::canDeleteAny();
    }

    /**
     * Check if the current user can restore a specific record.
     */
    public static function canRestore(Model $record): bool
    {
        return static::canEdit($record);
    }

    /**
     * Check if the current user can restore any records.
     */
    public static function canRestoreAny(): bool
    {
        $user = auth()->user();

        if (!$user) {
            return false;
        }

        if (method_exists($user, 'hasPermission')) {
            return $user->hasPermission(static::getEditPermission());
        }

        return false;
    }

    /**
     * Check if the current user can replicate a specific record.
     */
    public static function canReplicate(Model $record): bool
    {
        return static::canCreate();
    }

    /**
     * Check if the current user can reorder records.
     */
    public static function canReorder(): bool
    {
        return static::canEdit(new (static::getModel()));
    }
}
