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

test('permissions index page loads successfully', function () {
    $this->get(route('permissions.index'))
        ->assertSuccessful()
        ->assertSee(__('Permissions'));
});

test('permissions index displays permissions in table', function () {
    Permission::create(['name' => 'permission.assign-test'])
        ->assignRole('admin');

    Livewire::test('pages.permissions.index')
        ->assertSee('dashboard.view')
        ->assertSee('permission.assign')
        ->assertSee('permission.assign-test');
});

test('permissions can be searched by name', function () {
    $userPermission = Permission::create(['name' => 'user.special']);
    $rolePermission = Permission::create(['name' => 'role.special']);

    Livewire::test('pages.permissions.index')
        ->set('search', 'user.special')
        ->assertSee($userPermission->name)
        ->assertDontSee($rolePermission->name);
});

test('permissions can be sorted by columns', function () {
    $permissionA = Permission::create(['name' => 'zzz.alpha']);
    $permissionB = Permission::create(['name' => 'zzz.beta']);

    $component = Livewire::test('pages.permissions.index')
        ->set('sortBy', ['column' => 'name', 'direction' => 'desc']);

    // Verify that sorting is working by checking the data structure
    $permissions = $component->instance()->permissions();
    expect($permissions->first()->name)->toBe($permissionB->name);
});

test('permission can be deleted successfully when not assigned', function () {
    $testPermission = Permission::create(['name' => 'deletable.permission']);

    Livewire::test('pages.permissions.index')
        ->call('delete', $testPermission)
        ->assertSet('modal', false)
        ->assertSuccessful();

    $this->assertDatabaseMissing('permissions', [
        'id' => $testPermission->id,
    ]);
});

test('permission cannot be deleted when assigned to roles', function () {
    $testPermission = Permission::create(['name' => 'assigned.permission']);
    $testRole = Role::create(['name' => 'test-role']);
    $testRole->givePermissionTo($testPermission);

    // The super-admin bypasses authorization but the business logic should still prevent deletion
    Livewire::test('pages.permissions.index')
        ->call('delete', $testPermission);

    // The permission should still exist because it has roles assigned
    $this->assertDatabaseHas('permissions', [
        'id' => $testPermission->id,
    ]);
});

test('permission cannot be deleted when assigned to users', function () {
    $testPermission = Permission::create(['name' => 'user.assigned.permission']);
    $testUser = User::factory()->active()->create();
    $testUser->givePermissionTo($testPermission);

    // The super-admin bypasses authorization but the business logic should still prevent deletion
    Livewire::test('pages.permissions.index')
        ->call('delete', $testPermission);

    // The permission should still exist because it has users assigned
    $this->assertDatabaseHas('permissions', [
        'id' => $testPermission->id,
    ]);
});

test('filters can be cleared', function () {
    Livewire::test('pages.permissions.index')
        ->set('search', 'test')
        ->call('clear')
        ->assertSet('search', '');
});

test('pagination works correctly', function () {
    collect(range(1, 15))->each(fn ($i) => Permission::create(['name' => "test.permission.{$i}"]));

    $component = Livewire::test('pages.permissions.index');

    $permissions = $component->instance()->permissions();
    expect($permissions->count())->toBe(10)
        ->and($permissions->total())
        ->toBeGreaterThan(10);
});

test('drawer opens and closes for filters', function () {
    Livewire::test('pages.permissions.index')
        ->set('drawer', true)
        ->assertSet('drawer', true)
        ->set('drawer', false)
        ->assertSet('drawer', false);
});

test('unauthorized user cannot access permissions index', function () {
    $regularUser = User::factory()->active()->create();
    $regularUser->assignRole('user');

    $this->actingAs($regularUser)
        ->get(route('permissions.index'))
        ->assertForbidden();
});

test('user with permission.list permission can access permissions index', function () {
    $permissionManagerUser = User::factory()->active()->create(['email' => 'permissionmanager@admin.com']);
    $permissionManagerUser->assignRole('permission-manager');

    $this->actingAs($permissionManagerUser)
        ->get(route('permissions.index'))
        ->assertSuccessful();
});

test('only authorized users can see create button', function () {
    $permissionManagerUser = User::factory()->active()->create(['email' => 'permissionmanager@admin.com']);
    $permissionManagerUser->assignRole('permission-manager');

    $this->actingAs($permissionManagerUser)
        ->get(route('permissions.index'))
        ->assertSee(__('Create'));
});
