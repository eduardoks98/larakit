<?php

return [
    /*
    |--------------------------------------------------------------------------
    | User Model
    |--------------------------------------------------------------------------
    |
    | The user model class that will use the HasProfile trait.
    |
    */
    'user_model' => env('PERMISSIONS_USER_MODEL', 'App\\Models\\User'),

    /*
    |--------------------------------------------------------------------------
    | Table Names
    |--------------------------------------------------------------------------
    |
    | Database table names for profiles, permissions, and the pivot table.
    |
    */
    'tables' => [
        'profiles' => 'profiles',
        'permissions' => 'permissions',
        'pivot' => 'profile_permissions',
    ],

    /*
    |--------------------------------------------------------------------------
    | Permission Enum
    |--------------------------------------------------------------------------
    |
    | The enum class that defines all available permissions.
    | Must implement Eduardoks98\Permissions\Contracts\PermissionEnum.
    |
    | Example: App\Enums\PermissionType::class
    |
    */
    'permission_enum' => env('PERMISSIONS_ENUM', null),

    /*
    |--------------------------------------------------------------------------
    | Default Profile
    |--------------------------------------------------------------------------
    |
    | The name of the default profile assigned to new users.
    |
    */
    'default_profile' => 'Usuario',

    /*
    |--------------------------------------------------------------------------
    | Super Admin Bypass
    |--------------------------------------------------------------------------
    |
    | When enabled, profiles with is_admin=true bypass all permission checks.
    |
    */
    'super_admin_bypass' => true,

    /*
    |--------------------------------------------------------------------------
    | Cache Settings
    |--------------------------------------------------------------------------
    |
    | Configure permission caching for better performance.
    |
    */
    'cache' => [
        'enabled' => env('PERMISSIONS_CACHE_ENABLED', true),
        'ttl' => env('PERMISSIONS_CACHE_TTL', 3600), // 1 hour
        'prefix' => 'permissions_',
    ],

    /*
    |--------------------------------------------------------------------------
    | Admin Panel Configuration
    |--------------------------------------------------------------------------
    |
    | Configure the admin panel routes and middleware.
    |
    */
    'admin_middleware' => ['web', 'auth'],
    'admin_prefix' => 'admin',
    'admin_layout' => 'layouts.admin',

    /*
    |--------------------------------------------------------------------------
    | Profile Foreign Key
    |--------------------------------------------------------------------------
    |
    | The foreign key column name on the users table.
    |
    */
    'profile_foreign_key' => 'profile_id',
];
