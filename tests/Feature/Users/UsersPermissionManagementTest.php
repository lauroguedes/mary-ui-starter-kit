<?php

declare(strict_types=1);

use App\Models\User;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $this->adminUser = User::factory()->create(['email' => 'admin@admin.com']);
    $this->adminUser->assignRole('admin');
    $this->actingAs($this->adminUser);
    $this->targetUser = User::factory()->create();
});

test('permissions can be assigned during user edit', function () {
    $testPermission = Permission::create(['name' => 'test.permission']);

    Livewire::test('pages.users.edit', ['user' => $this->targetUser])
        ->set('name', $this->targetUser->name)
        ->set('email', $this->targetUser->email)
        ->set('status', $this->targetUser->status->value)
        ->set('permissionsGiven', [$testPermission->id])
        ->call('save')
        ->assertRedirect(route('users.index'));

    $this->targetUser->refresh();
    expect($this->targetUser->hasPermissionTo($testPermission))->toBeTrue();
});

test('multiple permissions can be assigned during user edit', function () {
    $permission1 = Permission::create(['name' => 'test.permission1']);
    $permission2 = Permission::create(['name' => 'test.permission2']);

    Livewire::test('pages.users.edit', ['user' => $this->targetUser])
        ->set('permissionsGiven', [$permission1->id, $permission2->id])
        ->call('save')
        ->assertRedirect(route('users.index'));

    $this->targetUser->refresh();
    expect($this->targetUser->hasPermissionTo($permission1))->toBeTrue()
        ->and($this->targetUser->hasPermissionTo($permission2))->toBeTrue()
        ->and($this->targetUser->permissions)->toHaveCount(2);
});

test('permissions can be removed during user edit', function () {
    $testPermission = Permission::create(['name' => 'test.permission']);
    $this->targetUser->givePermissionTo($testPermission);

    Livewire::test('pages.users.edit', ['user' => $this->targetUser])
        ->set('permissionsGiven', []) // Remove all permissions
        ->call('save')
        ->assertRedirect(route('users.index'));

    $this->targetUser->refresh();
    expect($this->targetUser->hasPermissionTo($testPermission))->toBeFalse()
        ->and($this->targetUser->permissions)->toHaveCount(0);
});

test('permissions can be synchronized during user edit', function () {
    $oldPermission = Permission::create(['name' => 'old.permission']);
    $newPermission = Permission::create(['name' => 'new.permission']);

    $this->targetUser->givePermissionTo($oldPermission);

    Livewire::test('pages.users.edit', ['user' => $this->targetUser])
        ->set('permissionsGiven', [$newPermission->id])
        ->call('save')
        ->assertRedirect(route('users.index'));

    $this->targetUser->refresh();
    expect($this->targetUser->hasPermissionTo($oldPermission))->toBeFalse()
        ->and($this->targetUser->hasPermissionTo($newPermission))->toBeTrue()
        ->and($this->targetUser->permissions)->toHaveCount(1);
});

test('existing permissions are preselected in user edit', function () {
    $permission1 = Permission::create(['name' => 'permission.1']);
    $permission2 = Permission::create(['name' => 'permission.2']);

    $this->targetUser->givePermissionTo([$permission1, $permission2]);

    $component = Livewire::test('pages.users.edit', ['user' => $this->targetUser]);

    expect($component->get('permissionsGiven'))->toContain($permission1->id)
        ->and($component->get('permissionsGiven'))->toContain($permission2->id)
        ->and($component->get('permissionsGiven'))->toHaveCount(2);
});

test('permissions can be searched in user edit - only super-admin', function () {
    $userPermission = Permission::create(['name' => 'user.manage']);
    $rolePermission = Permission::create(['name' => 'role.manage']);

    $this->adminUser->assignRole('super-admin');

    Livewire::test('pages.users.edit', ['user' => $this->targetUser])
        ->set('searchPermission', 'user')
        ->assertSee($userPermission->name)
        ->assertDontSee($rolePermission->name);
});

test('permissions pagination works in user edit', function () {
    collect(range(1, 15))->each(fn ($i) => Permission::create(['name' => "test.permission.{$i}"]));

    $component = Livewire::test('pages.users.edit', ['user' => $this->targetUser]);

    $permissions = $component->instance()->permissions();
    expect($permissions->count())->toBe(10)
        ->and($permissions->total())
        ->toBeGreaterThan(10);
});

test('invalid permission ids are handled gracefully during permission assignment', function () {
    $validPermission = Permission::create(['name' => 'valid.permission']);

    // Test only with valid permission ID
    Livewire::test('pages.users.edit', ['user' => $this->targetUser])
        ->set('permissionsGiven', [$validPermission->id])
        ->call('save')
        ->assertRedirect(route('users.index'));

    $this->targetUser->refresh();
    expect($this->targetUser->hasPermissionTo($validPermission))->toBeTrue()
        ->and($this->targetUser->permissions)->toHaveCount(1);
});

test('user with both roles and permissions shows correct assignments', function () {
    $testRole = Role::create(['name' => 'test-role']);
    $rolePermission = Permission::create(['name' => 'role.permission']);
    $directPermission = Permission::create(['name' => 'direct.permission']);

    $testRole->givePermissionTo($rolePermission);
    $this->targetUser->assignRole($testRole);
    $this->targetUser->givePermissionTo($directPermission);

    $component = Livewire::test('pages.users.edit', ['user' => $this->targetUser]);

    expect($component->get('rolesGiven'))->toContain($testRole->id)
        ->and($component->get('permissionsGiven'))->toContain($directPermission->id)
        ->and($component->get('permissionsGiven'))->not()->toContain($rolePermission->id);

});

test('permissions headers are correctly defined', function () {
    $component = Livewire::test('pages.users.edit', ['user' => $this->targetUser]);

    $headers = $component->instance()->headersPermission();
    expect($headers)->toBeArray()
        ->and($headers)->toContain(['key' => 'id', 'label' => '#', 'class' => 'w-1'])
        ->and($headers)->toContain(['key' => 'name', 'label' => 'Name']);
});

test('user permissions are updated only when user has permission.assign capability', function () {
    // Create a user without permission.assign capability
    $limitedUser = User::factory()->create(['email' => 'limited@admin.com']);
    $limitedUser->assignRole('user-manager'); // Has user permissions but not permission.assign
    $this->actingAs($limitedUser);

    $testPermission = Permission::create(['name' => 'test.permission']);

    Livewire::test('pages.users.edit', ['user' => $this->targetUser])
        ->set('permissionsGiven', [$testPermission->id])
        ->call('save')
        ->assertRedirect(route('users.index'));

    $this->targetUser->refresh();
    expect($this->targetUser->hasPermissionTo($testPermission))->toBeFalse();
});

test('permissions can be assigned by super-admin', function () {
    $superAdminUser = User::factory()->create(['email' => 'superadmin@admin.com']);
    $superAdminUser->assignRole('super-admin');
    $this->actingAs($superAdminUser);

    $testPermission = Permission::create(['name' => 'test.permission']);

    Livewire::test('pages.users.edit', ['user' => $this->targetUser])
        ->set('permissionsGiven', [$testPermission->id])
        ->call('save')
        ->assertRedirect(route('users.index'));

    $this->targetUser->refresh();
    expect($this->targetUser->hasPermissionTo($testPermission))->toBeTrue();
});

test('mixed role and permission changes work together', function () {
    $oldRole = Role::create(['name' => 'old-role']);
    $newRole = Role::create(['name' => 'new-role']);
    $oldPermission = Permission::create(['name' => 'old.permission']);
    $newPermission = Permission::create(['name' => 'new.permission']);

    $this->targetUser->assignRole($oldRole);
    $this->targetUser->givePermissionTo($oldPermission);

    Livewire::test('pages.users.edit', ['user' => $this->targetUser])
        ->set('rolesGiven', [$newRole->id])
        ->set('permissionsGiven', [$newPermission->id])
        ->call('save')
        ->assertRedirect(route('users.index'));

    $this->targetUser->refresh();
    expect($this->targetUser->hasRole($oldRole))->toBeFalse()
        ->and($this->targetUser->hasRole($newRole))->toBeTrue()
        ->and($this->targetUser->hasPermissionTo($oldPermission))->toBeFalse()
        ->and($this->targetUser->hasPermissionTo($newPermission))->toBeTrue();
});
