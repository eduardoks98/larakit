# Laravel RBAC Permissions Package

Role-Based Access Control (RBAC) system for Laravel with Filament 3 integration. Provides profiles with granular permissions using a simple `prefix:module:action` format.

## Installation

```bash
composer require eduardoks98/permissions
```

## Configuration

Publish the configuration file:

```bash
php artisan vendor:publish --tag=permissions-config
```

Publish and run migrations:

```bash
php artisan vendor:publish --tag=permissions-migrations
php artisan migrate
```

## Environment Variables

```env
PERMISSIONS_USER_MODEL=App\Models\User
PERMISSIONS_ENUM=App\Enums\PermissionType
PERMISSIONS_CACHE_ENABLED=true
PERMISSIONS_CACHE_TTL=3600
PERMISSIONS_FILAMENT_ENABLED=true
```

## Setup

### 1. Create Permission Enum

Create an enum that implements `PermissionEnum`:

```php
<?php

namespace App\Enums;

use Eduardoks98\Permissions\Contracts\PermissionEnum;

enum PermissionType: string implements PermissionEnum
{
    // Dashboard
    case ADMIN_DASHBOARD_VIEW = 'admin:dashboard:view';

    // Users
    case ADMIN_USERS_VIEW = 'admin:users:view';
    case ADMIN_USERS_CREATE = 'admin:users:create';
    case ADMIN_USERS_EDIT = 'admin:users:edit';
    case ADMIN_USERS_DELETE = 'admin:users:delete';

    // Posts
    case ADMIN_POSTS_VIEW = 'admin:posts:view';
    case ADMIN_POSTS_CREATE = 'admin:posts:create';
    case ADMIN_POSTS_EDIT = 'admin:posts:edit';
    case ADMIN_POSTS_DELETE = 'admin:posts:delete';

    public function label(): string
    {
        return match($this) {
            self::ADMIN_DASHBOARD_VIEW => 'Visualizar Dashboard',
            self::ADMIN_USERS_VIEW => 'Visualizar Usuários',
            self::ADMIN_USERS_CREATE => 'Criar Usuários',
            self::ADMIN_USERS_EDIT => 'Editar Usuários',
            self::ADMIN_USERS_DELETE => 'Excluir Usuários',
            self::ADMIN_POSTS_VIEW => 'Visualizar Posts',
            self::ADMIN_POSTS_CREATE => 'Criar Posts',
            self::ADMIN_POSTS_EDIT => 'Editar Posts',
            self::ADMIN_POSTS_DELETE => 'Excluir Posts',
        };
    }

    public function module(): string
    {
        $parts = explode(':', $this->value);
        return strtoupper($parts[1] ?? 'GENERAL');
    }

    public static function groupedByModule(): array
    {
        $grouped = [];
        foreach (self::cases() as $case) {
            $module = $case->module();
            $grouped[$module][] = $case;
        }
        return $grouped;
    }

    public static function moduleLabel(string $module): string
    {
        return match(strtoupper($module)) {
            'DASHBOARD' => 'Dashboard',
            'USERS' => 'Usuários',
            'POSTS' => 'Posts',
            default => ucfirst(strtolower($module)),
        };
    }
}
```

### 2. Add Migration for User Profile

```php
Schema::table('users', function (Blueprint $table) {
    $table->foreignId('profile_id')->nullable()->constrained()->nullOnDelete();
});
```

### 3. Add HasProfile Trait to User

```php
use Eduardoks98\Permissions\Traits\HasProfile;

class User extends Authenticatable
{
    use HasProfile;
}
```

### 4. Sync Permissions

```bash
php artisan permissions:sync --seed
```

## Usage

### Checking Permissions

```php
// Single permission
if ($user->hasPermission('admin:users:view')) {
    // ...
}

// All permissions required
if ($user->hasPermissions(['admin:users:view', 'admin:users:edit'])) {
    // ...
}

// Any permission
if ($user->hasAnyPermission(['admin:users:view', 'admin:posts:view'])) {
    // ...
}

// Check if admin
if ($user->isAdmin()) {
    // ...
}
```

### Using Helper Functions

```php
// Check current user's permission
if (has_permission('admin:users:view')) {
    // ...
}

// Check any permission
if (has_any_permission(['admin:users:view', 'admin:posts:view'])) {
    // ...
}

// Check if current user is admin
if (is_admin()) {
    // ...
}

// Get current profile
$profile = current_profile();

// Get all user permissions
$permissions = user_permissions();
```

### Using Middleware

```php
// Require ALL permissions
Route::middleware('permission:admin:users:view,admin:users:edit')
    ->get('/users', [UserController::class, 'index']);

// Require ANY permission
Route::middleware('any-permission:admin:users:view,admin:posts:view')
    ->get('/dashboard', [DashboardController::class, 'index']);
```

### Filament Resource Authorization

```php
use Eduardoks98\Permissions\Traits\HasResourcePermissions;

class UserResource extends Resource
{
    use HasResourcePermissions;

    protected static function getViewPermission(): string
    {
        return 'admin:users:view';
    }

    protected static function getCreatePermission(): string
    {
        return 'admin:users:create';
    }

    protected static function getEditPermission(): string
    {
        return 'admin:users:edit';
    }

    protected static function getDeletePermission(): string
    {
        return 'admin:users:delete';
    }
}
```

### Profile Management

```php
use Eduardoks98\Permissions\Models\Profile;

// Create a profile
$profile = Profile::create([
    'name' => 'Moderator',
    'description' => 'Content moderators',
    'is_admin' => false,
]);

// Sync permissions
$profile->syncPermissions([
    'admin:posts:view',
    'admin:posts:edit',
    'admin:posts:delete',
]);

// Grant a permission
$profile->grantPermission('admin:users:view');

// Revoke a permission
$profile->revokePermission('admin:users:view');

// Assign profile to user
$user->assignProfile('Moderator');
$user->assignProfileById($profile->id);
$user->assignDefaultProfile();
```

### Using PermissionService

```php
use Eduardoks98\Permissions\Services\PermissionService;

$service = app(PermissionService::class);

// Sync permissions from enum
$result = $service->syncPermissionsFromEnum();

// Get permissions grouped by module
$grouped = $service->getPermissionsGroupedByModule();

// Get options for Filament forms
$options = $service->getPermissionOptionsGroupedForFilament();

// Create profile with permissions
$profile = $service->createProfile(
    'Support',
    'Support team',
    false,
    ['admin:tickets:view', 'admin:tickets:edit']
);

// Seed default profiles
$created = $service->seedDefaultProfiles();
```

## Filament Admin Panel

The package includes a ProfileResource for managing profiles in Filament:

- List profiles with user/permission counts
- Create/edit profiles with grouped permission checkboxes
- Admin toggle that hides permission selection
- Soft delete support

### Customizing Navigation

```php
// config/permissions.php
'filament' => [
    'enabled' => true,
    'navigation_group' => 'Settings',
    'navigation_icon' => 'heroicon-o-shield-check',
    'navigation_sort' => 100,
    'navigation_label' => 'Profiles',
    'model_label' => 'Profile',
    'plural_model_label' => 'Profiles',
],
```

## Artisan Commands

```bash
# Sync permissions from enum
php artisan permissions:sync

# Sync and seed default profiles
php artisan permissions:sync --seed

# Fresh sync (delete all first)
php artisan permissions:sync --fresh
```

## Super Admin Bypass

Profiles with `is_admin = true` bypass all permission checks. This can be disabled:

```php
// config/permissions.php
'super_admin_bypass' => false,
```

## Caching

Permissions are cached for performance. Configure in `config/permissions.php`:

```php
'cache' => [
    'enabled' => true,
    'ttl' => 3600, // seconds
    'prefix' => 'permissions_',
],
```

Clear cache programmatically:

```php
$profile->clearPermissionCache();
// or
app(PermissionService::class)->clearCache();
```

## License

MIT License
