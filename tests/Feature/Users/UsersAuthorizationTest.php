<?php

declare(strict_types=1);

use App\Enums\UserStatus;
use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

use function Pest\Livewire\livewire;

/**
 * User Authorization Tests
 *
 * This test file consolidates all authorization-related tests including:
 * - Role assignment authorization
 * - Permission assignment authorization
 * - User CRUD operation authorization
 * - Self-operation restrictions
 * - Super-admin security rules
 */

// ============================================================================
// ROLE ASSIGNMENT AUTHORIZATION
// ============================================================================

test('user without role.assign permission cannot assign roles during creation', function () {
    $userManagerUser = User::factory()->active()->create(['email' => 'usermanager@admin.com']);
    $userManagerUser->assignRole('user-manager');
    $this->actingAs($userManagerUser);

    $testRole = Role::create(['name' => 'test-role']);

    livewire('pages.users.create')
        ->set('name', 'John Doe')
        ->set('email', 'john@example.com')
        ->set('status', UserStatus::ACTIVE->value)
        ->set('rolesGiven', [$testRole->id])
        ->call('save');

    $user = User::where('email', 'john@example.com')->first();
    expect($user)->not()->toBeNull()
        ->and($user->hasRole($testRole))->toBeFalse();
});

test('user with role.assign permission can assign roles during creation', function () {
    $userManagerUser = User::factory()->active()->create(['email' => 'usermanager@admin.com']);
    $userManagerUser->assignRole('user-manager');
    $userManagerUser->givePermissionTo('role.assign');
    $this->actingAs($userManagerUser);

    $testRole = Role::create(['name' => 'test-role']);

    livewire('pages.users.create')
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
    $userManagerUser = User::factory()->active()->create(['email' => 'usermanager@admin.com']);
    $userManagerUser->assignRole('user-manager');
    $userManagerUser->revokePermissionTo('role.assign');
    $this->actingAs($userManagerUser);

    $targetUser = User::factory()->active()->create();
    $testRole = Role::create(['name' => 'test-role']);

    livewire('pages.users.edit', ['user' => $targetUser])
        ->set('rolesGiven', [$testRole->id])
        ->call('save')
        ->assertRedirect(route('users.index'));

    $targetUser->refresh();
    expect($targetUser->hasRole($testRole))->toBeFalse();
});

test('user with role.assign permission can modify roles during edit', function () {
    $userManagerUser = User::factory()->active()->create(['email' => 'usermanager@admin.com']);
    $userManagerUser->assignRole('user-manager');
    $userManagerUser->givePermissionTo('role.assign');
    $this->actingAs($userManagerUser);

    $targetUser = User::factory()->active()->create();
    $testRole = Role::create(['name' => 'test-role']);

    livewire('pages.users.edit', ['user' => $targetUser])
        ->set('rolesGiven', [$testRole->id])
        ->call('save')
        ->assertRedirect(route('users.index'));

    $targetUser->refresh();
    expect($targetUser->hasRole($testRole))->toBeTrue();
});

// ============================================================================
// PERMISSION ASSIGNMENT AUTHORIZATION
// ============================================================================

test('user without permission.assign permission cannot assign permissions during edit', function () {
    $userManagerUser = User::factory()->active()->create(['email' => 'usermanager@admin.com']);
    $userManagerUser->assignRole('user-manager');
    $this->actingAs($userManagerUser);

    $targetUser = User::factory()->active()->create();
    $testPermission = Permission::create(['name' => 'test.permission']);

    livewire('pages.users.edit', ['user' => $targetUser])
        ->set('permissionsGiven', [$testPermission->id])
        ->call('save')
        ->assertRedirect(route('users.index'));

    $targetUser->refresh();
    expect($targetUser->hasPermissionTo($testPermission))->toBeFalse();
});

test('user with permission.assign permission can assign permissions during edit', function () {
    $superAdminUser = User::factory()->active()->create(['email' => 'superadmin@admin.com']);
    $superAdminUser->assignRole('super-admin');
    $this->actingAs($superAdminUser);

    $targetUser = User::factory()->active()->create();
    $testPermission = Permission::create(['name' => 'test.permission']);

    livewire('pages.users.edit', ['user' => $targetUser])
        ->set('permissionsGiven', [$testPermission->id])
        ->call('save')
        ->assertRedirect(route('users.index'));

    $targetUser->refresh();
    expect($targetUser->hasPermissionTo($testPermission))->toBeTrue();
});

// ============================================================================
// USER OPERATION AUTHORIZATION
// ============================================================================

test('user without user.create permission cannot access create page', function () {
    $regularUser = User::factory()->active()->create();
    $this->actingAs($regularUser);

    $this->get(route('users.create'))
        ->assertForbidden();
});

test('user without user.update permission cannot access edit page', function () {
    $regularUser = User::factory()->active()->create();
    $this->actingAs($regularUser);

    $targetUser = User::factory()->active()->create();

    $this->get(route('users.edit', $targetUser))
        ->assertForbidden();
});

test('user without user.list permission cannot access index page', function () {
    $regularUser = User::factory()->active()->create();
    $this->actingAs($regularUser);

    $this->get(route('users.index'))
        ->assertForbidden();
});

test('user without user.delete permission cannot delete users', function () {
    $regularUser = User::factory()->active()->create();
    $this->actingAs($regularUser);

    $targetUser = User::factory()->active()->create();

    livewire('pages.users.index')
        ->call('delete', $targetUser);

    $this->assertDatabaseHas('users', [
        'id' => $targetUser->id,
    ]);
});

// ============================================================================
// SELF-OPERATION RESTRICTIONS
// ============================================================================

test('user cannot delete themselves from user index', function () {
    $superAdminUser = User::factory()->active()->create(['email' => 'superadmin@admin.com', 'avatar' => null]);
    $superAdminUser->assignRole('super-admin');
    $this->actingAs($superAdminUser);

    livewire('pages.users.index')
        ->call('delete', $superAdminUser);

    $this->assertDatabaseHas('users', [
        'id' => $superAdminUser->id,
    ]);
});

test('user is redirected to profile when trying to edit themselves', function () {
    $userManagerUser = User::factory()->active()->create(['email' => 'usermanager@admin.com']);
    $userManagerUser->assignRole('user-manager');
    $this->actingAs($userManagerUser);

    $this->get(route('users.edit', $userManagerUser))
        ->assertRedirect(route('settings.profile'));
});

// ============================================================================
// ROLE VISIBILITY & PERMISSION LEVELS
// ============================================================================

test('users with different permission levels see different role options', function () {
    $adminUser = User::factory()->active()->create(['email' => 'admin@admin.com']);
    $adminUser->assignRole('admin');

    $superAdminUser = User::factory()->active()->create(['email' => 'superadmin@admin.com']);
    $superAdminUser->assignRole('super-admin');

    $this->actingAs($adminUser);
    $adminComponent = livewire('pages.users.create');
    $adminRoles = $adminComponent->instance()->roles();
    $adminRoleNames = $adminRoles->pluck('name')->toArray();
    expect($adminRoleNames)->not()->toContain('super-admin');

    $this->actingAs($superAdminUser);
    $superAdminComponent = livewire('pages.users.create');
    $superAdminRoles = $superAdminComponent->instance()->roles();
    $superAdminRoleNames = $superAdminRoles->pluck('name')->toArray();
    expect($superAdminRoleNames)->toContain('super-admin');
});

test('authorization is checked on each save operation', function () {
    $userManagerUser = User::factory()->active()->create(['email' => 'usermanager@admin.com']);
    $userManagerUser->assignRole('user-manager');
    $this->actingAs($userManagerUser);

    $component = livewire('pages.users.create')
        ->set('name', 'John Doe')
        ->set('email', 'john@example.com')
        ->set('status', UserStatus::ACTIVE->value);

    $userManagerUser->removeRole('user-manager');

    $component->call('save');

    $this->assertDatabaseMissing('users', [
        'email' => 'john@example.com',
    ]);
});

test('mixed permission scenarios work correctly', function () {
    $mixedUser = User::factory()->active()->create(['email' => 'mixed@admin.com']);
    $mixedUser->assignRole('user-manager');
    $mixedUser->givePermissionTo('role.assign');
    $this->actingAs($mixedUser);

    $targetUser = User::factory()->active()->create();
    $testRole = Role::create(['name' => 'test-role']);
    $testPermission = Permission::create(['name' => 'test.permission']);

    livewire('pages.users.edit', ['user' => $targetUser])
        ->set('rolesGiven', [$testRole->id])
        ->set('permissionsGiven', [$testPermission->id])
        ->call('save')
        ->assertRedirect(route('users.index'));

    $targetUser->refresh();
    expect($targetUser->hasRole($testRole))
        ->toBeTrue()
        ->and($targetUser->hasPermissionTo($testPermission))
        ->toBeFalse();
});

// ============================================================================
// SUPER-ADMIN SECURITY
// ============================================================================

test('non-super-admin cannot assign super-admin role during user creation', function () {
    $adminUser = User::factory()->active()->create(['email' => 'admin@admin.com']);
    $adminUser->assignRole('admin');
    $this->actingAs($adminUser);

    $component = livewire('pages.users.create');
    $roles = $component->instance()->roles();
    $roleNames = $roles->pluck('name')->toArray();
    expect($roleNames)->not()->toContain('super-admin');
});

test('super-admin can assign super-admin role during user creation', function () {
    $superAdminUser = User::factory()->active()->create(['email' => 'superadmin@admin.com']);
    $superAdminUser->assignRole('super-admin');
    $this->actingAs($superAdminUser);

    $superAdminRole = Role::where('name', 'super-admin')->first();

    livewire('pages.users.create')
        ->set('name', 'John Doe')
        ->set('email', 'john@example.com')
        ->set('status', UserStatus::ACTIVE->value)
        ->set('rolesGiven', [$superAdminRole->id])
        ->call('save')
        ->assertRedirect(route('users.index'));

    $user = User::where('email', 'john@example.com')->first();
    expect($user->hasRole('super-admin'))->toBeTrue();
});

test('non-super-admin cannot assign super-admin role during user edit', function () {
    $adminUser = User::factory()->active()->create(['email' => 'admin@admin.com']);
    $adminUser->assignRole('admin');
    $this->actingAs($adminUser);

    $targetUser = User::factory()->active()->create();

    $component = livewire('pages.users.edit', ['user' => $targetUser]);
    $roles = $component->instance()->roles();
    $roleNames = $roles->pluck('name')->toArray();
    expect($roleNames)->not()->toContain('super-admin');
});

test('super-admin can assign super-admin role during user edit', function () {
    $superAdminUser = User::factory()->active()->create(['email' => 'superadmin@admin.com']);
    $superAdminUser->assignRole('super-admin');
    $this->actingAs($superAdminUser);

    $targetUser = User::factory()->active()->create();
    $superAdminRole = Role::where('name', 'super-admin')->first();

    livewire('pages.users.edit', ['user' => $targetUser])
        ->set('rolesGiven', [$superAdminRole->id])
        ->call('save')
        ->assertRedirect(route('users.index'));

    $targetUser->refresh();
    expect($targetUser->hasRole('super-admin'))->toBeTrue();
});

test('unauthorized users cannot delete super-admin users', function () {
    $adminUser = User::factory()->active()->create(['email' => 'admin@admin.com']);
    $adminUser->assignRole('admin');
    $this->actingAs($adminUser);

    $superAdminUser = User::factory()->active()->create(['email' => 'superadmin@admin.com']);
    $superAdminUser->assignRole('super-admin');

    livewire('pages.users.index')
        ->call('delete', $superAdminUser);

    $this->assertDatabaseHas('users', [
        'email' => 'superadmin@admin.com',
    ]);
});

test('super-admin can delete other super-admin users', function () {
    $superAdminUser1 = User::factory()->active()->create(['email' => 'superadmin1@admin.com']);
    $superAdminUser1->assignRole('super-admin');
    $this->actingAs($superAdminUser1);

    $superAdminUser2 = User::factory()->active()->create(['email' => 'superadmin2@admin.com']);
    $superAdminUser2->assignRole('super-admin');

    livewire('pages.users.index')
        ->call('delete', $superAdminUser2);

    $this->assertDatabaseMissing('users', [
        'id' => $superAdminUser2->id,
    ]);
});

test('super-admin users trying to delete themselves are redirected to profile', function () {
    $superAdminUser = User::factory()->active()->create(['email' => 'superadmin@admin.com']);
    $superAdminUser->assignRole('super-admin');
    $this->actingAs($superAdminUser);

    livewire('pages.users.index')
        ->call('delete', $superAdminUser)
        ->assertRedirectToRoute('settings.profile');

    $this->assertDatabaseHas('users', [
        'id' => $superAdminUser->id,
    ]);
});

test('super-admin has full access to user operations', function () {
    $superAdminUser = User::factory()->active()->create(['email' => 'superadmin@admin.com']);
    $superAdminUser->assignRole('super-admin');
    $this->actingAs($superAdminUser);

    $this->get(route('users.index'))->assertSuccessful();
    $this->get(route('users.create'))->assertSuccessful();

    $targetUser = User::factory()->active()->create();
    $this->get(route('users.edit', $targetUser))->assertSuccessful();
});

test('regular users respect normal authorization for user operations', function () {
    $regularUser = User::factory()->active()->create(['email' => 'regular@user.com']);
    $regularUser->assignRole('user');
    $this->actingAs($regularUser);

    $this->get(route('users.index'))->assertForbidden();
    $this->get(route('users.create'))->assertForbidden();

    $targetUser = User::factory()->active()->create();
    $this->get(route('users.edit', $targetUser))->assertForbidden();
});
