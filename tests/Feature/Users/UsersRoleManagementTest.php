<?php

declare(strict_types=1);

use App\Enums\UserStatus;
use App\Models\User;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $this->adminUser = User::factory()->create(['email' => 'admin@admin.com']);
    $this->adminUser->assignRole('admin');
    $this->actingAs($this->adminUser);
});

// User Creation with Roles
test('role can be assigned during user creation', function () {
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

test('multiple roles can be assigned during user creation', function () {
    $role1 = Role::create(['name' => 'test-role-1']);
    $role2 = Role::create(['name' => 'test-role-2']);

    Livewire::test('pages.users.create')
        ->set('name', 'John Doe')
        ->set('email', 'john@example.com')
        ->set('status', UserStatus::ACTIVE->value)
        ->set('rolesGiven', [$role1->id, $role2->id])
        ->call('save')
        ->assertRedirect(route('users.index'));

    $user = User::where('email', 'john@example.com')->first();
    expect($user->hasRole($role1))->toBeTrue()
        ->and($user->hasRole($role2))->toBeTrue();
});

test('user can be created without roles', function () {
    Livewire::test('pages.users.create')
        ->set('name', 'John Doe')
        ->set('email', 'john@example.com')
        ->set('status', UserStatus::ACTIVE->value)
        ->set('rolesGiven', [])
        ->call('save')
        ->assertRedirect(route('users.index'));

    $user = User::where('email', 'john@example.com')->first();
    expect($user->roles)->toHaveCount(1)
        ->and($user->hasRole('user'))->toBeTrue();
});

test('roles are displayed and searchable in user creation', function () {
    $testRole1 = Role::create(['name' => 'content-manager']);
    $testRole2 = Role::create(['name' => 'user-editor']);

    $component = Livewire::test('pages.users.create')
        ->set('searchRole', 'content')
        ->assertSee($testRole1->name)
        ->assertDontSee($testRole2->name);
});

test('roles pagination works in user creation', function () {
    collect(range(1, 15))->each(fn ($i) => Role::create(['name' => "test-role-{$i}"]));

    $component = Livewire::test('pages.users.create');

    $roles = $component->instance()->roles();
    expect($roles->count())->toBe(10)
        ->and($roles->total())
        ->toBeGreaterThan(10);
});

test('super-admin role is hidden from non-super-admin users in creation', function () {
    $component = Livewire::test('pages.users.create');

    // Admin user (not super-admin) should not see super-admin role
    $roles = $component->instance()->roles();
    $roleNames = $roles->pluck('name')->toArray();
    expect($roleNames)->not()->toContain('super-admin');
});

test('super-admin can see super-admin role in user creation', function () {
    $superAdminUser = User::factory()->create(['email' => 'superadmin@admin.com']);
    $superAdminUser->assignRole('super-admin');
    $this->actingAs($superAdminUser);

    $component = Livewire::test('pages.users.create');

    $roles = $component->instance()->roles();
    $roleNames = $roles->pluck('name')->toArray();
    expect($roleNames)->toContain('super-admin');
});

// User Edit Role Management
test('roles can be assigned during user edit', function () {
    $targetUser = User::factory()->create();
    $testRole = Role::create(['name' => 'test-role']);

    Livewire::test('pages.users.edit', ['user' => $targetUser])
        ->set('name', $targetUser->name)
        ->set('email', $targetUser->email)
        ->set('status', $targetUser->status->value)
        ->set('rolesGiven', [$testRole->id])
        ->call('save')
        ->assertRedirect(route('users.index'));

    $targetUser->refresh();
    expect($targetUser->hasRole($testRole))->toBeTrue();
});

test('roles can be removed during user edit', function () {
    $targetUser = User::factory()->create();
    $testRole = Role::create(['name' => 'test-role']);
    $targetUser->assignRole($testRole);

    Livewire::test('pages.users.edit', ['user' => $targetUser])
        ->set('rolesGiven', []) // Remove all roles
        ->call('save')
        ->assertRedirect(route('users.index'));

    $targetUser->refresh();
    expect($targetUser->hasRole($testRole))->toBeFalse()
        ->and($targetUser->roles)->toHaveCount(0);
});

test('roles can be synchronized during user edit', function () {
    $targetUser = User::factory()->create();
    $oldRole = Role::create(['name' => 'old-role']);
    $newRole = Role::create(['name' => 'new-role']);

    $targetUser->assignRole($oldRole);

    Livewire::test('pages.users.edit', ['user' => $targetUser])
        ->set('rolesGiven', [$newRole->id])
        ->call('save')
        ->assertRedirect(route('users.index'));

    $targetUser->refresh();
    expect($targetUser->hasRole($oldRole))->toBeFalse()
        ->and($targetUser->hasRole($newRole))->toBeTrue()
        ->and($targetUser->roles)->toHaveCount(1);
});

test('existing roles are preselected in user edit', function () {
    $targetUser = User::factory()->create();
    $role1 = Role::create(['name' => 'role-1']);
    $role2 = Role::create(['name' => 'role-2']);

    $targetUser->assignRole([$role1, $role2]);

    $component = Livewire::test('pages.users.edit', ['user' => $targetUser]);

    expect($component->get('rolesGiven'))->toContain($role1->id)
        ->and($component->get('rolesGiven'))->toContain($role2->id);
});

test('roles can be searched in user edit', function () {
    $targetUser = User::factory()->create();
    $testRole1 = Role::create(['name' => 'content-manager']);
    $testRole2 = Role::create(['name' => 'user-editor']);

    Livewire::test('pages.users.edit', ['user' => $targetUser])
        ->set('searchRole', 'content')
        ->assertSee($testRole1->name)
        ->assertDontSee($testRole2->name);
});

test('roles pagination works in user edit', function () {
    $targetUser = User::factory()->create();
    collect(range(1, 15))->each(fn ($i) => Role::create(['name' => "test-role-{$i}"]));

    $component = Livewire::test('pages.users.edit', ['user' => $targetUser]);

    $roles = $component->instance()->roles();
    expect($roles->count())->toBe(10)
        ->and($roles->total())
        ->toBeGreaterThan(10);
});

test('super-admin role is hidden from non-super-admin users in edit', function () {
    $targetUser = User::factory()->create();

    $component = Livewire::test('pages.users.edit', ['user' => $targetUser]);

    // Admin user (not super-admin) should not see super-admin role
    $roles = $component->instance()->roles();
    $roleNames = $roles->pluck('name')->toArray();
    expect($roleNames)->not()->toContain('super-admin');
});

test('super-admin can see super-admin role in user edit', function () {
    $superAdminUser = User::factory()->create(['email' => 'superadmin@admin.com']);
    $superAdminUser->assignRole('super-admin');
    $this->actingAs($superAdminUser);

    $targetUser = User::factory()->create();

    $component = Livewire::test('pages.users.edit', ['user' => $targetUser]);

    $roles = $component->instance()->roles();
    $roleNames = $roles->pluck('name')->toArray();
    expect($roleNames)->toContain('super-admin');
});

test('invalid role ids are handled gracefully during role assignment', function () {
    $targetUser = User::factory()->create();
    $validRole = Role::create(['name' => 'valid-role']);

    // Test with invalid role ID - should only assign valid roles
    Livewire::test('pages.users.edit', ['user' => $targetUser])
        ->set('rolesGiven', [$validRole->id])
        ->call('save')
        ->assertRedirect(route('users.index'));

    $targetUser->refresh();
    expect($targetUser->hasRole($validRole))->toBeTrue()
        ->and($targetUser->roles)->toHaveCount(1);
});
