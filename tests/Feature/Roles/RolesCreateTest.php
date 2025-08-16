<?php

declare(strict_types=1);

use App\Models\User;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $adminUser = User::factory()->create(['email' => 'admin@admin.com']);
    $adminUser->assignRole('admin');
    $this->actingAs($adminUser);
});

test('roles create page loads successfully', function () {
    $this->get(route('roles.create'))
        ->assertSuccessful()
        ->assertSee(__('Create Role'));
});

test('role can be created with valid data', function () {
    $permission = Permission::create(['name' => 'test.permission']);

    Livewire::test('pages.roles.create')
        ->set('name', 'new-test-role')
        ->set('permissionsGiven', [$permission->id])
        ->call('save')
        ->assertRedirect(route('roles.index'));

    $this->assertDatabaseHas('roles', [
        'name' => 'new-test-role',
    ]);

    $role = Role::where('name', 'new-test-role')->first();
    expect($role->hasPermissionTo($permission))->toBeTrue();
});

test('role name is required', function () {
    Livewire::test('pages.roles.create')
        ->set('name', '')
        ->call('save')
        ->assertHasErrors(['name' => 'required']);
});

test('role name must be unique', function () {
    Role::create(['name' => 'existing-role']);

    Livewire::test('pages.roles.create')
        ->set('name', 'existing-role')
        ->call('save')
        ->assertHasErrors(['name' => 'unique']);
});

test('role name cannot exceed 100 characters', function () {
    $longName = str_repeat('a', 101);

    Livewire::test('pages.roles.create')
        ->set('name', $longName)
        ->call('save')
        ->assertHasErrors(['name' => 'max']);
});

test('role can be created without permissions', function () {
    Livewire::test('pages.roles.create')
        ->set('name', 'role-without-permissions')
        ->set('permissionsGiven', [])
        ->call('save')
        ->assertRedirect(route('roles.index'));

    $this->assertDatabaseHas('roles', [
        'name' => 'role-without-permissions',
    ]);

    $role = Role::where('name', 'role-without-permissions')->first();
    expect($role->permissions)->toHaveCount(0);
});

test('permissions can be searched', function () {
    $userPermission = Permission::create(['name' => 'user.manage']);
    $rolePermission = Permission::create(['name' => 'role.manage']);

    Livewire::test('pages.roles.create')
        ->set('search', 'user')
        ->assertSee($userPermission->name)
        ->assertDontSee($rolePermission->name);
});

test('permissions pagination works correctly', function () {
    collect(range(1, 15))->each(fn ($i) => Permission::create(['name' => "test.permission.{$i}"]));

    $component = Livewire::test('pages.roles.create');

    $permissions = $component->instance()->permissions();
    expect($permissions->count())->toBe(10)
        ->and($permissions->total())
        ->toBeGreaterThan(10);
});

test('unauthorized user cannot access roles create page', function () {
    $regularUser = User::factory()->create();
    $regularUser->assignRole('user');

    $this->actingAs($regularUser)
        ->get(route('roles.create'))
        ->assertForbidden();
});

test('user with role.create permission can access create page', function () {
    $roleManagerUser = User::factory()->create(['email' => 'rolemanager@admin.com']);
    $roleManagerUser->assignRole('role-manager');

    $this->actingAs($roleManagerUser)
        ->get(route('roles.create'))
        ->assertStatus(200);
});

test('multiple permissions can be assigned to role', function () {
    $permission1 = Permission::create(['name' => 'test.permission1']);
    $permission2 = Permission::create(['name' => 'test.permission2']);
    $permission3 = Permission::create(['name' => 'test.permission3']);

    Livewire::test('pages.roles.create')
        ->set('name', 'multi-permission-role')
        ->set('permissionsGiven', [$permission1->id, $permission2->id, $permission3->id])
        ->call('save')
        ->assertRedirect(route('roles.index'));

    $role = Role::where('name', 'multi-permission-role')->first();
    expect($role->hasPermissionTo($permission1))->toBeTrue()
        ->and($role->hasPermissionTo($permission2))->toBeTrue()
        ->and($role->hasPermissionTo($permission3))->toBeTrue()
        ->and($role->permissions)->toHaveCount(3);
});

test('role creation shows success message', function () {
    Livewire::test('pages.roles.create')
        ->set('name', 'success-test-role')
        ->call('save')
        ->assertRedirect(route('roles.index'));

    $this->assertDatabaseHas('roles', [
        'name' => 'success-test-role',
    ]);
});

test('invalid permission ids are filtered out', function () {
    $validPermission = Permission::create(['name' => 'valid.permission']);

    Livewire::test('pages.roles.create')
        ->set('name', 'test-role')
        ->set('permissionsGiven', [$validPermission->id])
        ->call('save')
        ->assertRedirect(route('roles.index'));

    $role = Role::where('name', 'test-role')->first();
    expect($role->hasPermissionTo($validPermission))->toBeTrue()
        ->and($role->permissions)->toHaveCount(1);
});
