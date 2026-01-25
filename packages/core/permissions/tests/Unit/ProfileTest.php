<?php

use Eduardoks98\Permissions\Models\Profile;
use Eduardoks98\Permissions\Models\Permission;

beforeEach(function () {
    $this->artisan('migrate', ['--database' => 'testing']);
});

it('can create a profile', function () {
    $profile = Profile::create([
        'name' => 'Test Profile',
        'description' => 'Test description',
        'is_admin' => false,
    ]);

    expect($profile)->toBeInstanceOf(Profile::class)
        ->and($profile->name)->toBe('Test Profile')
        ->and($profile->is_admin)->toBeFalse();
});

it('can create an admin profile', function () {
    $profile = Profile::create([
        'name' => 'Admin',
        'is_admin' => true,
    ]);

    expect($profile->is_admin)->toBeTrue();
});

it('can attach permissions to profile', function () {
    $profile = Profile::create(['name' => 'Test']);

    $permission1 = Permission::create(['name' => 'admin:users:view']);
    $permission2 = Permission::create(['name' => 'admin:users:create']);

    $profile->permissions()->attach([$permission1->id, $permission2->id]);

    expect($profile->permissions)->toHaveCount(2);
});

it('checks if profile has permission', function () {
    $profile = Profile::create(['name' => 'Test']);
    $permission = Permission::create(['name' => 'admin:users:view']);

    $profile->permissions()->attach($permission->id);

    expect($profile->hasPermission('admin:users:view'))->toBeTrue()
        ->and($profile->hasPermission('admin:users:create'))->toBeFalse();
});

it('admin profile bypasses permission check', function () {
    $profile = Profile::create([
        'name' => 'Admin',
        'is_admin' => true,
    ]);

    expect($profile->hasPermission('admin:any:permission'))->toBeTrue();
});

it('can sync permissions', function () {
    $profile = Profile::create(['name' => 'Test']);

    Permission::create(['name' => 'admin:users:view']);
    Permission::create(['name' => 'admin:users:create']);
    Permission::create(['name' => 'admin:users:delete']);

    $profile->syncPermissions(['admin:users:view', 'admin:users:create']);

    expect($profile->permissions)->toHaveCount(2)
        ->and($profile->hasPermission('admin:users:view'))->toBeTrue()
        ->and($profile->hasPermission('admin:users:create'))->toBeTrue()
        ->and($profile->hasPermission('admin:users:delete'))->toBeFalse();
});

it('can get default profile', function () {
    Profile::create(['name' => 'Usuario']);
    Profile::create(['name' => 'Admin']);

    $default = Profile::getDefaultProfile();

    expect($default)->not->toBeNull()
        ->and($default->name)->toBe('Usuario');
});

it('can get admin profile', function () {
    Profile::create(['name' => 'Usuario']);
    Profile::create(['name' => 'Admin', 'is_admin' => true]);

    $admin = Profile::getAdminProfile();

    expect($admin)->not->toBeNull()
        ->and($admin->is_admin)->toBeTrue();
});
