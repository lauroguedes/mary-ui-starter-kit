<?php

declare(strict_types=1);

use App\Enums\UserStatus;
use App\Models\User;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

test('non-super-admin cannot assign super-admin role during user creation', function () {
    $adminUser = User::factory()->active()->create(['email' => 'admin@admin.com']);
    $adminUser->assignRole('admin');
    $this->actingAs($adminUser);

    // Admin user should not even see super-admin role in role options
    $component = Livewire::test('pages.users.create');
    $roles = $component->instance()->roles();
    $roleNames = $roles->pluck('name')->toArray();
    expect($roleNames)->not()->toContain('super-admin');
});

test('super-admin can assign super-admin role during user creation', function () {
    $superAdminUser = User::factory()->active()->create(['email' => 'superadmin@admin.com']);
    $superAdminUser->assignRole('super-admin');
    $this->actingAs($superAdminUser);

    $superAdminRole = Role::where('name', 'super-admin')->first();

    Livewire::test('pages.users.create')
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

    // Admin user should not even see super-admin role in role options
    $component = Livewire::test('pages.users.edit', ['user' => $targetUser]);
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

    Livewire::test('pages.users.edit', ['user' => $targetUser])
        ->set('rolesGiven', [$superAdminRole->id])
        ->call('save')
        ->assertRedirect(route('users.index'));

    $targetUser->refresh();
    expect($targetUser->hasRole('super-admin'))->toBeTrue();
});

test('super-admin can remove super-admin role from another super-admin user', function () {
    $superAdminUser = User::factory()->active()->create(['email' => 'superadmin1@admin.com']);
    $superAdminUser->assignRole('super-admin');
    $this->actingAs($superAdminUser);

    $anotherSuperAdmin = User::factory()->active()->create();
    $anotherSuperAdmin->assignRole('super-admin');

    Livewire::test('pages.users.edit', ['user' => $anotherSuperAdmin])
        ->set('rolesGiven', []) // Remove all roles
        ->call('save')
        ->assertRedirect(route('users.index'));

    $anotherSuperAdmin->refresh();
    expect($anotherSuperAdmin->hasRole('super-admin'))->toBeFalse();
});

test('super-admin role appears with warning decoration in user creation', function () {
    $superAdminUser = User::factory()->active()->create(['email' => 'superadmin@admin.com']);
    $superAdminUser->assignRole('super-admin');
    $this->actingAs($superAdminUser);

    $component = Livewire::test('pages.users.create');

    $rowDecoration = $component->instance()->rowDecoration();
    expect($rowDecoration)->toHaveKey('bg-warning/20');

    // Test that the decoration function works correctly
    $superAdminRole = Role::where('name', 'super-admin')->first();
    $normalRole = Role::create(['name' => 'normal-role']);

    $decorationFunction = $rowDecoration['bg-warning/20'];
    expect($decorationFunction($superAdminRole))->toBeTrue()
        ->and($decorationFunction($normalRole))->toBeFalse();
});

test('super-admin role appears with warning decoration in user edit', function () {
    $superAdminUser = User::factory()->active()->create(['email' => 'superadmin@admin.com']);
    $superAdminUser->assignRole('super-admin');
    $this->actingAs($superAdminUser);

    $targetUser = User::factory()->active()->create();

    $component = Livewire::test('pages.users.edit', ['user' => $targetUser]);

    $rowDecoration = $component->instance()->rowDecoration();
    expect($rowDecoration)->toHaveKey('bg-warning/20');

    // Test that the decoration function works correctly
    $superAdminRole = Role::where('name', 'super-admin')->first();
    $normalRole = Role::create(['name' => 'normal-role']);

    $decorationFunction = $rowDecoration['bg-warning/20'];
    expect($decorationFunction($superAdminRole))->toBeTrue()
        ->and($decorationFunction($normalRole))->toBeFalse();
});

test('unauthorized users cannot delete super-admin users', function () {
    $adminUser = User::factory()->active()->create(['email' => 'admin@admin.com']);
    $adminUser->assignRole('admin');
    $this->actingAs($adminUser);

    $superAdminUser = User::factory()->active()->create(['email' => 'superadmin@admin.com']);
    $superAdminUser->assignRole('super-admin');

    // Test that admin user cannot delete super-admin
    Livewire::test('pages.users.index')
        ->call('delete', $superAdminUser);

    // Super-admin user should still exist
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

    Livewire::test('pages.users.index')
        ->call('delete', $superAdminUser2);

    $this->assertDatabaseMissing('users', [
        'id' => $superAdminUser2->id,
    ]);
});

test('super-admin users try delete themselves and is redirect to profile', function () {
    $superAdminUser = User::factory()->active()->create(['email' => 'superadmin@admin.com']);
    $superAdminUser->assignRole('super-admin');
    $this->actingAs($superAdminUser);

    Livewire::test('pages.users.index')
        ->call('delete', $superAdminUser)
        ->assertRedirectToRoute('settings.profile');

    $this->assertDatabaseHas('users', [
        'id' => $superAdminUser->id,
    ]);
});

test('gate before allows super-admin full access to user operations', function () {
    $superAdminUser = User::factory()->active()->create(['email' => 'superadmin@admin.com']);
    $superAdminUser->assignRole('super-admin');
    $this->actingAs($superAdminUser);

    // Super-admin should be able to access all user pages
    $this->get(route('users.index'))->assertSuccessful();
    $this->get(route('users.create'))->assertSuccessful();

    $targetUser = User::factory()->active()->create();
    $this->get(route('users.edit', $targetUser))->assertSuccessful();
});

test('regular users respect normal authorization for user operations', function () {
    $regularUser = User::factory()->active()->create(['email' => 'regular@user.com']);
    $regularUser->assignRole('user');
    $this->actingAs($regularUser);

    // Regular user should be forbidden from user management pages
    $this->get(route('users.index'))->assertForbidden();
    $this->get(route('users.create'))->assertForbidden();

    $targetUser = User::factory()->active()->create();
    $this->get(route('users.edit', $targetUser))->assertForbidden();
});
