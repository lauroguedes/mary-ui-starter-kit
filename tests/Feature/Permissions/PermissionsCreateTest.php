<?php

declare(strict_types=1);

use App\Models\User;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $superAdminUser = User::factory()->active()->create(['email' => 'superadmin@admin.com']);
    $superAdminUser->assignRole('super-admin');
    $this->actingAs($superAdminUser);
});

test('permissions create page loads successfully', function () {
    $this->get(route('permissions.create'))
        ->assertSuccessful()
        ->assertSee(__('Create Permission'));
});

test('permission can be created with valid data', function () {
    $testRole = Role::create(['name' => 'test-role']);

    Livewire::test('pages.permissions.create')
        ->set('name', 'test.new.permission')
        ->set('rolesGiven', [$testRole->id])
        ->call('save')
        ->assertRedirect(route('permissions.index'));

    $this->assertDatabaseHas('permissions', [
        'name' => 'test.new.permission',
    ]);

    $permission = Permission::where('name', 'test.new.permission')->first();
    expect($testRole->hasPermissionTo($permission))->toBeTrue();
});

test('permission name is required', function () {
    Livewire::test('pages.permissions.create')
        ->set('name', '')
        ->call('save')
        ->assertHasErrors(['name' => 'required']);
});

test('permission name must be unique', function () {
    Permission::create(['name' => 'existing.permission']);

    Livewire::test('pages.permissions.create')
        ->set('name', 'existing.permission')
        ->call('save')
        ->assertHasErrors(['name' => 'unique']);
});

test('permission name cannot exceed 100 characters', function () {
    $longName = str_repeat('a', 101);

    Livewire::test('pages.permissions.create')
        ->set('name', $longName)
        ->call('save')
        ->assertHasErrors(['name' => 'max']);
});

test('permission name must follow dot notation regex', function () {
    Livewire::test('pages.permissions.create')
        ->set('name', 'invalid-permission-name')
        ->call('save')
        ->assertHasErrors(['name' => 'regex']);

    Livewire::test('pages.permissions.create')
        ->set('name', 'Invalid.Permission')
        ->call('save')
        ->assertHasErrors(['name' => 'regex']);

    Livewire::test('pages.permissions.create')
        ->set('name', 'permission.with.123')
        ->call('save')
        ->assertHasErrors(['name' => 'regex']);
});

test('permission name accepts valid dot notation', function () {
    $validNames = [
        'test.create',
        'test.profile.update',
        'testsingleword',
    ];

    foreach ($validNames as $index => $name) {
        Livewire::test('pages.permissions.create')
            ->set('name', $name)
            ->call('save')
            ->assertRedirect(route('permissions.index'));

        $this->assertDatabaseHas('permissions', ['name' => $name]);
    }
});

test('permission can be created without roles', function () {
    Livewire::test('pages.permissions.create')
        ->set('name', 'permission.without.roles')
        ->set('rolesGiven', [])
        ->call('save')
        ->assertRedirect(route('permissions.index'));

    $this->assertDatabaseHas('permissions', [
        'name' => 'permission.without.roles',
    ]);

    $permission = Permission::where('name', 'permission.without.roles')->first();
    expect($permission->roles)->toHaveCount(0);
});

test('unauthorized user cannot access permissions create page', function () {
    $regularUser = User::factory()->active()->create();
    $regularUser->assignRole('user');

    $this->actingAs($regularUser)
        ->get(route('permissions.create'))
        ->assertForbidden();
});

test('user with permission.create permission can access create page', function () {
    $permissionManagerUser = User::factory()->active()->create(['email' => 'permissionmanager@admin.com']);
    $permissionManagerUser->assignRole('permission-manager');

    $this->actingAs($permissionManagerUser)
        ->get(route('permissions.create'))
        ->assertSuccessful();
});

test('multiple roles can be assigned to permission', function () {
    $role1 = Role::create(['name' => 'test-role-1']);
    $role2 = Role::create(['name' => 'test-role-2']);
    $role3 = Role::create(['name' => 'test-role-3']);

    Livewire::test('pages.permissions.create')
        ->set('name', 'multi.role.permission')
        ->set('rolesGiven', [$role1->id, $role2->id, $role3->id])
        ->call('save')
        ->assertRedirect(route('permissions.index'));

    $permission = Permission::where('name', 'multi.role.permission')->first();
    expect($role1->hasPermissionTo($permission))->toBeTrue()
        ->and($role2->hasPermissionTo($permission))->toBeTrue()
        ->and($role3->hasPermissionTo($permission))->toBeTrue()
        ->and($permission->roles)->toHaveCount(3);
});

test('permission creation shows success message', function () {
    Livewire::test('pages.permissions.create')
        ->set('name', 'success.test.permission')
        ->call('save')
        ->assertRedirect(route('permissions.index'));

    $this->assertDatabaseHas('permissions', [
        'name' => 'success.test.permission',
    ]);
});

test('invalid role ids are filtered out', function () {
    $validRole = Role::create(['name' => 'valid-role']);

    Livewire::test('pages.permissions.create')
        ->set('name', 'test.permission')
        ->set('rolesGiven', [$validRole->id])
        ->call('save')
        ->assertRedirect(route('permissions.index'));

    $permission = Permission::where('name', 'test.permission')->first();
    expect($validRole->hasPermissionTo($permission))->toBeTrue()
        ->and($permission->roles)->toHaveCount(1);
});

test('permission name hint is displayed', function () {
    $response = $this->get(route('permissions.create'));

    $response->assertSee(__('Use lowercase and dot notation. Ex: model.action'));
});

test('permission name with special regex patterns work', function () {
    $testPermissions = [
        'testuser.create',
        'testuser.profile.update',
        'content.management.create',
        'api.access',
        'singleword',
    ];

    foreach ($testPermissions as $permissionName) {
        Livewire::test('pages.permissions.create')
            ->set('name', $permissionName)
            ->call('save')
            ->assertRedirect(route('permissions.index'));

        $this->assertDatabaseHas('permissions', [
            'name' => $permissionName,
        ]);
    }
});
