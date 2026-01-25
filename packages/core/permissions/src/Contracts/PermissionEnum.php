<?php

namespace Eduardoks98\Permissions\Contracts;

/**
 * Interface for permission enum classes.
 *
 * This interface should be implemented by the application's permission enum
 * to enable automatic synchronization and grouping of permissions.
 *
 * Example implementation:
 *
 * enum PermissionType: string implements PermissionEnum
 * {
 *     case ADMIN_DASHBOARD_VIEW = 'admin:dashboard:view';
 *     case ADMIN_USERS_VIEW = 'admin:users:view';
 *     case ADMIN_USERS_CREATE = 'admin:users:create';
 *
 *     public function label(): string
 *     {
 *         return match($this) {
 *             self::ADMIN_DASHBOARD_VIEW => 'Visualizar Dashboard',
 *             self::ADMIN_USERS_VIEW => 'Visualizar Usuários',
 *             self::ADMIN_USERS_CREATE => 'Criar Usuários',
 *         };
 *     }
 *
 *     public function module(): string
 *     {
 *         $parts = explode(':', $this->value);
 *         return strtoupper($parts[1] ?? 'GENERAL');
 *     }
 *
 *     public static function groupedByModule(): array
 *     {
 *         $grouped = [];
 *         foreach (self::cases() as $case) {
 *             $module = $case->module();
 *             $grouped[$module][] = $case;
 *         }
 *         return $grouped;
 *     }
 *
 *     public static function moduleLabel(string $module): string
 *     {
 *         return match(strtoupper($module)) {
 *             'DASHBOARD' => 'Dashboard',
 *             'USERS' => 'Usuários',
 *             default => ucfirst(strtolower($module)),
 *         };
 *     }
 * }
 */
interface PermissionEnum
{
    /**
     * Get the human-readable label for the permission.
     */
    public function label(): string;

    /**
     * Get the module name from the permission.
     */
    public function module(): string;

    /**
     * Get all permissions grouped by module.
     *
     * @return array<string, array<self>>
     */
    public static function groupedByModule(): array;

    /**
     * Get the human-readable label for a module.
     */
    public static function moduleLabel(string $module): string;
}
