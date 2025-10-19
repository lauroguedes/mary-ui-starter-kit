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
    $this->testPermission = Permission::create(['name' => 'edit.test.permission']);
});

test('permissions edit page loads successfully', function () {
    $this->get(route('permissions.edit', $this->testPermission))
        ->assertSuccessful()
        ->assertSee(__('Update Permission'));
});

test('permission edit page displays existing permission data', function () {
    $component = Livewire::test('pages.permissions.edit', ['permission' => $this->testPermission]);

    $component->assertSet('name', $this->testPermission->name);
});

test('permission can be updated with valid data', function () {
    $testRole = Role::create(['name' => 'test-role']);

    Livewire::test('pages.permissions.edit', ['permission' => $this->testPermission])
        ->set('name', 'updated.permission.name')
        ->set('rolesGiven', [$testRole->id])
        ->call('save')
        ->assertRedirect(route('permissions.index'));

    $this->testPermission->refresh();
    expect($this->testPermission->name)->toBe('updated.permission.name')
        ->and($testRole->hasPermissionTo($this->testPermission))->toBeTrue();
});

test('permission name is required for update', function () {
    Livewire::test('pages.permissions.edit', ['permission' => $this->testPermission])
        ->set('name', '')
        ->call('save')
        ->assertHasErrors(['name' => 'required']);
});

test('permission name must be unique excluding current permission', function () {
    Permission::create(['name' => 'existing.permission']);

    Livewire::test('pages.permissions.edit', ['permission' => $this->testPermission])
        ->set('name', 'existing.permission')
        ->call('save')
        ->assertHasErrors(['name' => 'unique']);
});

test('permission can keep same name when updating', function () {
    $originalName = $this->testPermission->name;
    $testRole = Role::create(['name' => 'test-role']);

    Livewire::test('pages.permissions.edit', ['permission' => $this->testPermission])
        ->set('name', $originalName)
        ->set('rolesGiven', [$testRole->id])
        ->call('save')
        ->assertRedirect(route('permissions.index'));

    $this->testPermission->refresh();
    expect($this->testPermission->name)->toBe($originalName)
        ->and($testRole->hasPermissionTo($this->testPermission))->toBeTrue();
});

test('permission name must follow dot notation regex', function () {
    Livewire::test('pages.permissions.edit', ['permission' => $this->testPermission])
        ->set('name', 'invalid-permission')
        ->call('save')
        ->assertHasErrors(['name' => 'regex']);

    Livewire::test('pages.permissions.edit', ['permission' => $this->testPermission])
        ->set('name', 'Invalid.Permission')
        ->call('save')
        ->assertHasErrors(['name' => 'regex']);
});

test('permission name cannot exceed 100 characters', function () {
    $longName = str_repeat('a', 101);

    Livewire::test('pages.permissions.edit', ['permission' => $this->testPermission])
        ->set('name', $longName)
        ->call('save')
        ->assertHasErrors(['name' => 'max']);
});

test('permission roles can be updated', function () {
    $role1 = Role::create(['name' => 'test-role-1']);
    $role2 = Role::create(['name' => 'test-role-2']);

    $role1->givePermissionTo($this->testPermission);

    Livewire::test('pages.permissions.edit', ['permission' => $this->testPermission])
        ->set('rolesGiven', [$role2->id])
        ->call('save')
        ->assertRedirect(route('permissions.index'));

    expect($role1->hasPermissionTo($this->testPermission))->toBeFalse()
        ->and($role2->hasPermissionTo($this->testPermission))->toBeTrue()
        ->and($this->testPermission->roles)->toHaveCount(1);
});

test('all roles can be removed from permission', function () {
    $testRole = Role::create(['name' => 'test-role']);
    $testRole->givePermissionTo($this->testPermission);

    Livewire::test('pages.permissions.edit', ['permission' => $this->testPermission])
        ->set('rolesGiven', [])
        ->call('save')
        ->assertRedirect(route('permissions.index'));

    $this->testPermission->refresh();
    expect($this->testPermission->roles)->toHaveCount(0);
});

test('existing roles are preselected', function () {
    $role1 = Role::create(['name' => 'test-role-1']);
    $role2 = Role::create(['name' => 'test-role-2']);
    $role1->givePermissionTo($this->testPermission);
    $role2->givePermissionTo($this->testPermission);

    $component = Livewire::test('pages.permissions.edit', ['permission' => $this->testPermission]);

    expect($component->get('rolesGiven'))->toContain($role1->id)
        ->and($component->get('rolesGiven'))->toContain($role2->id)
        ->and($component->get('rolesGiven'))->toHaveCount(2);
});

test('roles can be searched during edit', function () {
    $userRole = Role::create(['name' => 'user-role']);
    $adminRole = Role::create(['name' => 'admin-role']);

    Livewire::test('pages.permissions.edit', ['permission' => $this->testPermission])
        ->set('search', 'user')
        ->assertSee($userRole->name)
        ->assertDontSee($adminRole->name);
});

test('unauthorized user cannot access permissions edit page', function () {
    $regularUser = User::factory()->active()->create();
    $regularUser->assignRole('user');

    $this->actingAs($regularUser)
        ->get(route('permissions.edit', $this->testPermission))
        ->assertForbidden();
});

test('user with permission.update permission can access edit page', function () {
    $permissionManagerUser = User::factory()->active()->create(['email' => 'permissionmanager@admin.com']);
    $permissionManagerUser->assignRole('permission-manager');

    $this->actingAs($permissionManagerUser)
        ->get(route('permissions.edit', $this->testPermission))
        ->assertSuccessful();
});

test('permission update shows success message', function () {
    Livewire::test('pages.permissions.edit', ['permission' => $this->testPermission])
        ->set('name', 'updated.permission')
        ->call('save')
        ->assertRedirect(route('permissions.index'));

    $this->testPermission->refresh();
    expect($this->testPermission->name)->toBe('updated.permission');
});

test('roles pagination works on edit page', function () {
    collect(range(1, 15))->each(fn ($i) => Role::create(['name' => "test-role-{$i}"]));

    $component = Livewire::test('pages.permissions.edit', ['permission' => $this->testPermission]);

    $roles = $component->instance()->roles();
    expect($roles->count())->toBe(10)
        ->and($roles->total())
        ->toBeGreaterThan(10);
});

test('multiple roles can be added and removed', function () {
    $existingRole = Role::create(['name' => 'existing-role']);
    $newRole1 = Role::create(['name' => 'new-role-1']);
    $newRole2 = Role::create(['name' => 'new-role-2']);

    $existingRole->givePermissionTo($this->testPermission);

    Livewire::test('pages.permissions.edit', ['permission' => $this->testPermission])
        ->set('rolesGiven', [$newRole1->id, $newRole2->id])
        ->call('save')
        ->assertRedirect(route('permissions.index'));

    $this->testPermission->refresh();
    $existingRole->refresh();
    $newRole1->refresh();
    $newRole2->refresh();

    expect($existingRole->hasPermissionTo($this->testPermission))->toBeFalse()
        ->and($newRole1->hasPermissionTo($this->testPermission))->toBeTrue()
        ->and($newRole2->hasPermissionTo($this->testPermission))->toBeTrue()
        ->and($this->testPermission->roles)->toHaveCount(2);
});

test('permission name is disabled when assigned to users', function () {
    $testUser = User::factory()->active()->create();
    $testUser->givePermissionTo($this->testPermission);

    $response = $this->get(route('permissions.edit', $this->testPermission));

    $response->assertSee(__('The name edition is disabled because the permission is binding.'));
});

test('permission name is disabled when assigned to roles', function () {
    $testRole = Role::create(['name' => 'test-role']);
    $testRole->givePermissionTo($this->testPermission);

    $response = $this->get(route('permissions.edit', $this->testPermission));

    $response->assertSee(__('The name edition is disabled because the permission is binding.'));
});

test('permission name field shows proper hint', function () {
    $response = $this->get(route('permissions.edit', $this->testPermission));

    $response->assertSee(__('Use lowercase and dot notation. Ex: model.action'));
});

test('permission name validation works with various valid formats', function () {
    $validNames = [
        'testuser.create',
        'testuser.profile.update',
        'content.management.create',
        'api.access',
        'singleword',
    ];

    foreach ($validNames as $index => $permissionName) {
        $testPermission = Permission::create(['name' => "test.permission.{$index}"]);

        Livewire::test('pages.permissions.edit', ['permission' => $testPermission])
            ->set('name', $permissionName)
            ->call('save')
            ->assertRedirect(route('permissions.index'));

        $testPermission->refresh();
        expect($testPermission->name)->toBe($permissionName);
    }
});
