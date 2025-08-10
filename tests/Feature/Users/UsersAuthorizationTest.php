<?php

declare(strict_types=1);

use App\Enums\UserStatus;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
});

// Role Assignment Authorization Tests
test('user without role.assign permission cannot assign roles during creation', function () {
    $userManagerUser = User::factory()->create(['email' => 'usermanager@admin.com']);
    $userManagerUser->assignRole('user-manager');
    // user-manager role doesn't have role.assign permission by default
    $this->actingAs($userManagerUser);

    $testRole = Role::create(['name' => 'test-role']);

    Livewire::test('pages.users.create')
        ->set('name', 'John Doe')
        ->set('email', 'john@example.com')
        ->set('status', UserStatus::ACTIVE->value)
        ->set('rolesGiven', [$testRole->id])
        ->call('save');

    // User should be created but role should NOT be assigned due to lack of permission
    $user = User::where('email', 'john@example.com')->first();
    expect($user)->not()->toBeNull()
        ->and($user->hasRole($testRole))->toBeFalse();
});

test('user with role.assign permission can assign roles during creation', function () {
    $userManagerUser = User::factory()->create(['email' => 'usermanager@admin.com']);
    $userManagerUser->assignRole('user-manager');
    $userManagerUser->givePermissionTo('role.assign');
    $this->actingAs($userManagerUser);

    $testRole = Role::create(['name' => 'test-role']);

    Livewire::test('pages.users.create')
        ->set('name', 'John Doe')
        ->set('email', 'john@example.com')
        ->set('status', UserStatus::ACTIVE->value)
        ->set('rolesGiven', [$testRole->id])
        ->call('save')
        ->assertRedirect(route('users.index'));

    $user = User::where('email', 'john@example.com')->first();
    expect($user->hasRole($testRole))->toBeTrue();
});

test('user without role.assign permission cannot modify roles during edit', function () {
    $userManagerUser = User::factory()->create(['email' => 'usermanager@admin.com']);
    $userManagerUser->assignRole('user-manager');
    $userManagerUser->revokePermissionTo('role.assign'); // Remove role assignment permission
    $this->actingAs($userManagerUser);

    $targetUser = User::factory()->create();
    $testRole = Role::create(['name' => 'test-role']);

    Livewire::test('pages.users.edit', ['user' => $targetUser])
        ->set('rolesGiven', [$testRole->id])
        ->call('save')
        ->assertRedirect(route('users.index'));

    $targetUser->refresh();
    // Role should NOT be assigned
    expect($targetUser->hasRole($testRole))->toBeFalse();
});

test('user with role.assign permission can modify roles during edit', function () {
    $userManagerUser = User::factory()->create(['email' => 'usermanager@admin.com']);
    $userManagerUser->assignRole('user-manager');
    $userManagerUser->givePermissionTo('role.assign');
    $this->actingAs($userManagerUser);

    $targetUser = User::factory()->create();
    $testRole = Role::create(['name' => 'test-role']);

    Livewire::test('pages.users.edit', ['user' => $targetUser])
        ->set('rolesGiven', [$testRole->id])
        ->call('save')
        ->assertRedirect(route('users.index'));

    $targetUser->refresh();
    expect($targetUser->hasRole($testRole))->toBeTrue();
});

// Permission Assignment Authorization Tests
test('user without permission.assign permission cannot assign permissions during edit', function () {
    $userManagerUser = User::factory()->create(['email' => 'usermanager@admin.com']);
    $userManagerUser->assignRole('user-manager');
    // user-manager role doesn't have permission.assign by default
    $this->actingAs($userManagerUser);

    $targetUser = User::factory()->create();
    $testPermission = Permission::create(['name' => 'test.permission']);

    Livewire::test('pages.users.edit', ['user' => $targetUser])
        ->set('permissionsGiven', [$testPermission->id])
        ->call('save')
        ->assertRedirect(route('users.index'));

    $targetUser->refresh();
    // Permission should NOT be assigned
    expect($targetUser->hasPermissionTo($testPermission))->toBeFalse();
});

test('user with permission.assign permission can assign permissions during edit', function () {
    $superAdminUser = User::factory()->create(['email' => 'superadmin@admin.com']);
    $superAdminUser->assignRole('super-admin');
    $this->actingAs($superAdminUser);

    $targetUser = User::factory()->create();
    $testPermission = Permission::create(['name' => 'test.permission']);

    Livewire::test('pages.users.edit', ['user' => $targetUser])
        ->set('permissionsGiven', [$testPermission->id])
        ->call('save')
        ->assertRedirect(route('users.index'));

    $targetUser->refresh();
    expect($targetUser->hasPermissionTo($testPermission))->toBeTrue();
});

// User Operation Authorization Tests
test('user without user.create permission cannot access create page', function () {
    $regularUser = User::factory()->create();
    $regularUser->assignRole('user');
    $this->actingAs($regularUser);

    $this->get(route('users.create'))
        ->assertForbidden();
});

test('user without user.update permission cannot access edit page', function () {
    $regularUser = User::factory()->create();
    $regularUser->assignRole('user');
    $this->actingAs($regularUser);

    $targetUser = User::factory()->create();

    $this->get(route('users.edit', $targetUser))
        ->assertForbidden();
});

test('user without user.list permission cannot access index page', function () {
    $regularUser = User::factory()->create();
    $regularUser->assignRole('user');
    $this->actingAs($regularUser);

    $this->get(route('users.index'))
        ->assertForbidden();
});

test('user without user.delete permission cannot delete users', function () {
    $regularUser = User::factory()->create();
    $regularUser->assignRole('user');
    $this->actingAs($regularUser);

    $targetUser = User::factory()->create();

    Livewire::test('pages.users.index')
        ->call('delete', $targetUser);

    // User should still exist since delete was not authorized
    $this->assertDatabaseHas('users', [
        'id' => $targetUser->id,
    ]);
});

// Self-Operation Tests
test('user cannot delete themselves from user index', function () {
    $superAdminUser = User::factory()->create(['email' => 'superadmin@admin.com']);
    $superAdminUser->assignRole('super-admin');
    $this->actingAs($superAdminUser);

    Livewire::test('pages.users.index')
        ->call('delete', $superAdminUser);

    // User should still exist
    $this->assertDatabaseHas('users', [
        'id' => $superAdminUser->id,
    ]);
});

test('user can edit themselves if they have proper permissions', function () {
    $userManagerUser = User::factory()->create(['email' => 'usermanager@admin.com']);
    $userManagerUser->assignRole('user-manager');
    $this->actingAs($userManagerUser);

    $this->get(route('users.edit', $userManagerUser))
        ->assertSuccessful();
});

test('users with different permission levels see different role options', function () {
    $adminUser = User::factory()->create(['email' => 'admin@admin.com']);
    $adminUser->assignRole('admin');

    $superAdminUser = User::factory()->create(['email' => 'superadmin@admin.com']);
    $superAdminUser->assignRole('super-admin');

    // Admin user should not see super-admin role
    $this->actingAs($adminUser);
    $adminComponent = Livewire::test('pages.users.create');
    $adminRoles = $adminComponent->instance()->roles();
    $adminRoleNames = $adminRoles->pluck('name')->toArray();
    expect($adminRoleNames)->not()->toContain('super-admin');

    // Super-admin user should see super-admin role
    $this->actingAs($superAdminUser);
    $superAdminComponent = Livewire::test('pages.users.create');
    $superAdminRoles = $superAdminComponent->instance()->roles();
    $superAdminRoleNames = $superAdminRoles->pluck('name')->toArray();
    expect($superAdminRoleNames)->toContain('super-admin');
});

test('authorization is checked on each save operation', function () {
    $userManagerUser = User::factory()->create(['email' => 'usermanager@admin.com']);
    $userManagerUser->assignRole('user-manager');
    $this->actingAs($userManagerUser);

    // Start the component
    $component = Livewire::test('pages.users.create')
        ->set('name', 'John Doe')
        ->set('email', 'john@example.com')
        ->set('status', UserStatus::ACTIVE->value);

    // Remove the permission during the session
    $userManagerUser->revokePermissionTo('user.create');

    // The save should fail due to authorization, user should not be created
    $component->call('save');

    // User should not be created due to missing permission
    $this->assertDatabaseMissing('users', [
        'email' => 'john@example.com',
    ]);
});

test('mixed permission scenarios work correctly', function () {
    $mixedUser = User::factory()->create(['email' => 'mixed@admin.com']);
    $mixedUser->assignRole('user-manager'); // Has user permissions
    $mixedUser->givePermissionTo('role.assign'); // Add role assignment permission
    // But doesn't have permission.assign
    $this->actingAs($mixedUser);

    $targetUser = User::factory()->create();
    $testRole = Role::create(['name' => 'test-role']);
    $testPermission = Permission::create(['name' => 'test.permission']);

    Livewire::test('pages.users.edit', ['user' => $targetUser])
        ->set('rolesGiven', [$testRole->id])
        ->set('permissionsGiven', [$testPermission->id])
        ->call('save')
        ->assertRedirect(route('users.index'));

    $targetUser->refresh();
    // Role should be assigned (has role.assign)
    expect($targetUser->hasRole($testRole))->toBeTrue();
    // Permission should NOT be assigned (lacks permission.assign)
    expect($targetUser->hasPermissionTo($testPermission))->toBeFalse();
});
