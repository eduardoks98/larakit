<?php

use Eduardoks98\Permissions\Models\Permission;

beforeEach(function () {
    $this->artisan('migrate', ['--database' => 'testing']);
});

it('can create a permission', function () {
    $permission = Permission::create([
        'name' => 'admin:users:view',
        'description' => 'View users',
    ]);

    expect($permission)->toBeInstanceOf(Permission::class)
        ->and($permission->name)->toBe('admin:users:view');
});

it('extracts module from permission name', function () {
    $permission = Permission::create(['name' => 'admin:users:view']);

    expect($permission->module)->toBe('USERS');
});

it('extracts action from permission name', function () {
    $permission = Permission::create(['name' => 'admin:users:view']);

    expect($permission->action)->toBe('view');
});

it('extracts prefix from permission name', function () {
    $permission = Permission::create(['name' => 'admin:users:view']);

    expect($permission->prefix)->toBe('admin');
});

it('handles single segment permission names', function () {
    $permission = Permission::create(['name' => 'dashboard']);

    expect($permission->module)->toBe('DASHBOARD')
        ->and($permission->action)->toBe('dashboard')
        ->and($permission->prefix)->toBe('dashboard');
});

it('can get permissions by module', function () {
    Permission::create(['name' => 'admin:users:view']);
    Permission::create(['name' => 'admin:users:create']);
    Permission::create(['name' => 'admin:posts:view']);

    $userPermissions = Permission::getByModule('users');

    expect($userPermissions)->toHaveCount(2);
});

it('can get all permissions grouped by module', function () {
    Permission::create(['name' => 'admin:users:view']);
    Permission::create(['name' => 'admin:users:create']);
    Permission::create(['name' => 'admin:posts:view']);
    Permission::create(['name' => 'admin:posts:create']);

    $grouped = Permission::getAllGroupedByModule();

    expect($grouped)->toHaveKeys(['USERS', 'POSTS'])
        ->and($grouped['USERS'])->toHaveCount(2)
        ->and($grouped['POSTS'])->toHaveCount(2);
});

it('can get all unique modules', function () {
    Permission::create(['name' => 'admin:users:view']);
    Permission::create(['name' => 'admin:users:create']);
    Permission::create(['name' => 'admin:posts:view']);

    $modules = Permission::getModules();

    expect($modules)->toContain('USERS', 'POSTS')
        ->and($modules)->toHaveCount(2);
});

it('can create or update permission', function () {
    $permission1 = Permission::createOrUpdate('admin:users:view', 'View users');
    $permission2 = Permission::createOrUpdate('admin:users:view', 'View all users');

    expect($permission1->id)->toBe($permission2->id)
        ->and($permission2->description)->toBe('View all users');
});

it('can find permission by name', function () {
    Permission::create(['name' => 'admin:users:view']);

    $found = Permission::findByName('admin:users:view');
    $notFound = Permission::findByName('admin:users:delete');

    expect($found)->not->toBeNull()
        ->and($notFound)->toBeNull();
});
