<?php

declare(strict_types=1);

use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

use function Pest\Livewire\livewire;

beforeEach(function () {
    $adminUser = User::factory()->active()->create(['email' => 'admin@admin.com']);
    $adminUser->assignRole('admin');
    $this->actingAs($adminUser);
    $this->testRole = Role::create(['name' => 'edit-test-role']);
});

test('roles edit page loads successfully', function () {
    $this->get(route('roles.edit', $this->testRole))
        ->assertSuccessful()
        ->assertSee(__('Update Role'));
});

test('role edit page displays existing role data', function () {
    $component = livewire('pages.roles.edit', ['role' => $this->testRole]);

    $component->assertSet('name', $this->testRole->name);
});

test('role can be updated with valid data', function () {
    $permission = Permission::create(['name' => 'test.permission']);

    livewire('pages.roles.edit', ['role' => $this->testRole])
        ->set('name', 'updated-role-name')
        ->set('permissionsGiven', [$permission->id])
        ->call('save')
        ->assertRedirect(route('roles.index'));

    $this->testRole->refresh();
    expect($this->testRole->name)
        ->toBe('updated-role-name')
        ->and($this->testRole->hasPermissionTo($permission))
        ->toBeTrue();
});

test('role name is required for update', function () {
    livewire('pages.roles.edit', ['role' => $this->testRole])
        ->set('name', '')
        ->call('save')
        ->assertHasErrors(['name' => 'required']);
});

test('role name must be unique excluding current role', function () {
    $existingRole = Role::create(['name' => 'existing-role']);

    livewire('pages.roles.edit', ['role' => $this->testRole])
        ->set('name', 'existing-role')
        ->call('save')
        ->assertHasErrors(['name' => 'unique']);
});

test('role can keep same name when updating', function () {
    $originalName = $this->testRole->name;
    $permission = Permission::create(['name' => 'test.permission']);

    livewire('pages.roles.edit', ['role' => $this->testRole])
        ->set('name', $originalName)
        ->set('permissionsGiven', [$permission->id])
        ->call('save')
        ->assertRedirect(route('roles.index'));

    $this->testRole->refresh();
    expect($this->testRole->name)
        ->toBe($originalName)
        ->and($this->testRole->hasPermissionTo($permission))
        ->toBeTrue();
});

test('role name cannot exceed 100 characters', function () {
    $longName = str_repeat('a', 101);

    livewire('pages.roles.edit', ['role' => $this->testRole])
        ->set('name', $longName)
        ->call('save')
        ->assertHasErrors(['name' => 'max']);
});

test('role permissions can be updated', function () {
    $permission1 = Permission::create(['name' => 'test.permission1']);
    $permission2 = Permission::create(['name' => 'test.permission2']);

    $this->testRole->givePermissionTo($permission1);

    livewire('pages.roles.edit', ['role' => $this->testRole])
        ->set('permissionsGiven', [$permission2->id])
        ->call('save')
        ->assertRedirect(route('roles.index'));

    $this->testRole->refresh();
    expect($this->testRole->hasPermissionTo($permission1))->toBeFalse()
        ->and($this->testRole->hasPermissionTo($permission2))->toBeTrue()
        ->and($this->testRole->permissions)->toHaveCount(1);
});

test('all permissions can be removed from role', function () {
    $permission = Permission::create(['name' => 'test.permission']);
    $this->testRole->givePermissionTo($permission);

    livewire('pages.roles.edit', ['role' => $this->testRole])
        ->set('permissionsGiven', [])
        ->call('save')
        ->assertRedirect(route('roles.index'));

    $this->testRole->refresh();
    expect($this->testRole->permissions)->toHaveCount(0);
});

test('existing permissions are preselected', function () {
    $permission1 = Permission::create(['name' => 'test.permission1']);
    $permission2 = Permission::create(['name' => 'test.permission2']);
    $this->testRole->givePermissionTo([$permission1, $permission2]);

    $component = livewire('pages.roles.edit', ['role' => $this->testRole]);

    expect($component->get('permissionsGiven'))->toContain($permission1->id)
        ->and($component->get('permissionsGiven'))->toContain($permission2->id)
        ->and($component->get('permissionsGiven'))->toHaveCount(2);
});

test('permissions can be searched during edit', function () {
    $userPermission = Permission::create(['name' => 'user.manage']);
    $rolePermission = Permission::create(['name' => 'role.manage']);

    livewire('pages.roles.edit', ['role' => $this->testRole])
        ->set('search', 'user')
        ->assertSee($userPermission->name)
        ->assertDontSee($rolePermission->name);
});

test('unauthorized user cannot access roles edit page', function () {
    $regularUser = User::factory()->active()->create();
    $regularUser->assignRole('user');

    $this->actingAs($regularUser)
        ->get(route('roles.edit', $this->testRole))
        ->assertForbidden();
});

test('user with role.update permission can access edit page', function () {
    $roleManagerUser = User::factory()->active()->create(['email' => 'rolemanager@admin.com']);
    $roleManagerUser->assignRole('role-manager', 'user');

    $this->actingAs($roleManagerUser)
        ->get(route('roles.edit', $this->testRole))
        ->assertSuccessful();
});

test('role update shows success message', function () {
    livewire('pages.roles.edit', ['role' => $this->testRole])
        ->set('name', 'updated-role')
        ->call('save')
        ->assertRedirect(route('roles.index'));

    $this->testRole->refresh();
    expect($this->testRole->name)->toBe('updated-role');
});

test('permissions pagination works on edit page', function () {
    collect(range(1, 15))->each(fn ($i) => Permission::create(['name' => "test.permission.{$i}"]));

    $component = livewire('pages.roles.edit', ['role' => $this->testRole]);

    $permissions = $component->instance()->permissions();
    expect($permissions->count())->toBe(10)
        ->and($permissions->total())
        ->toBeGreaterThan(10);
});

test('multiple permissions can be added and removed', function () {
    $existingPermission = Permission::create(['name' => 'existing.permission']);
    $newPermission1 = Permission::create(['name' => 'new.permission1']);
    $newPermission2 = Permission::create(['name' => 'new.permission2']);

    $this->testRole->givePermissionTo($existingPermission);

    livewire('pages.roles.edit', ['role' => $this->testRole])
        ->set('permissionsGiven', [$newPermission1->id, $newPermission2->id])
        ->call('save')
        ->assertRedirect(route('roles.index'));

    $this->testRole->refresh();
    expect($this->testRole->hasPermissionTo($existingPermission))->toBeFalse()
        ->and($this->testRole->hasPermissionTo($newPermission1))->toBeTrue()
        ->and($this->testRole->hasPermissionTo($newPermission2))->toBeTrue()
        ->and($this->testRole->permissions)->toHaveCount(2);
});
