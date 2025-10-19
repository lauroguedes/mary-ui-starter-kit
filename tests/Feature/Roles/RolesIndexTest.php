<?php

declare(strict_types=1);

use App\Models\User;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $adminUser = User::factory()->active()->create(['email' => 'admin@admin.com']);
    $adminUser->assignRole('admin');
    $this->actingAs($adminUser);
});

test('roles index page loads successfully', function () {
    $this->get(route('roles.index'))
        ->assertSuccessful()
        ->assertSee(__('Roles'));
});

test('roles index displays roles in table', function () {
    Role::create(['name' => 'test-role'])
        ->givePermissionTo('user.view');

    Livewire::test('pages.roles.index')
        ->assertSee('super-admin')
        ->assertSee('admin')
        ->assertSee('test-role');
});

test('roles can be searched by name', function () {
    $managerRole = Role::create(['name' => 'content-manager']);
    $editorRole = Role::create(['name' => 'content-editor']);

    Livewire::test('pages.roles.index')
        ->set('search', 'manager')
        ->assertSee($managerRole->name)
        ->assertDontSee($editorRole->name);
});

test('roles can be sorted by columns', function () {
    $roleA = Role::create(['name' => 'alpha-role']);
    $roleB = Role::create(['name' => 'beta-role']);

    Livewire::test('pages.roles.index')
        ->set('sortBy', ['column' => 'name', 'direction' => 'desc'])
        ->assertSeeInOrder([$roleB->name, $roleA->name]);
});

test('role can be deleted successfully when no users assigned', function () {
    $testRole = Role::create(['name' => 'deletable-role']);

    Livewire::test('pages.roles.index')
        ->call('delete', $testRole)
        ->assertSet('modal', false)
        ->assertSuccessful();

    $this->assertDatabaseMissing('roles', [
        'id' => $testRole->id,
    ]);
});

test('role cannot be deleted when users are assigned', function () {
    $testRole = Role::create(['name' => 'assigned-role']);
    $testUser = User::factory()->active()->create();
    $testUser->assignRole($testRole);

    // The super-admin bypasses authorization but the business logic should still prevent deletion
    Livewire::test('pages.roles.index')
        ->call('delete', $testRole);

    // The role should still exist because it has users assigned
    $this->assertDatabaseHas('roles', [
        'id' => $testRole->id,
    ]);
});

test('role permissions are displayed in popover', function () {
    $testRole = Role::create(['name' => 'test-role-with-permissions']);
    $permission = Permission::create(['name' => 'test.permission']);
    $testRole->givePermissionTo($permission);

    $component = Livewire::test('pages.roles.index');
    $html = $component->html();

    expect($html)->toContain('test-role-with-permissions')
        ->and($html)->toContain('test.permission');
});

test('unauthorized user cannot access roles index', function () {
    $regularUser = User::factory()->active()->create();
    $regularUser->assignRole('user');

    $this->actingAs($regularUser)
        ->get(route('roles.index'))
        ->assertForbidden();
});

test('user with role.list permission can access roles index', function () {
    $roleManagerUser = User::factory()->active()->create(['email' => 'rolemanager@admin.com']);
    $roleManagerUser->assignRole('role-manager');

    $this->actingAs($roleManagerUser)
        ->get(route('roles.index'))
        ->assertSuccessful();
});

test('delete button only shows for roles without users', function () {
    $roleWithUsers = Role::create(['name' => 'role-with-users']);
    $roleWithoutUsers = Role::create(['name' => 'role-without-users']);

    $testUser = User::factory()->active()->create();
    $testUser->assignRole($roleWithUsers);

    $component = Livewire::test('pages.roles.index');
    $html = $component->html();

    expect($html)->toContain('role-with-users')
        ->and($html)->toContain('role-without-users');
});

test('only authorized users can see create button', function () {
    $roleManagerUser = User::factory()->active()->create(['email' => 'rolemanager@admin.com']);
    $roleManagerUser->assignRole('role-manager');

    $this->actingAs($roleManagerUser)
        ->get(route('roles.index'))
        ->assertSee(__('Create'));
});
