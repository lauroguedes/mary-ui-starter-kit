<?php

declare(strict_types=1);

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Livewire\Volt\Volt;

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    Storage::fake('public');
});

test('profile page is displayed', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('profile.view');

    $this->actingAs($user);

    $this->get('/settings/profile')->assertOk();
});

test('profile information can be updated', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('profile.update');

    $this->actingAs($user);

    $response = Volt::test('settings.profile')
        ->set('name', 'Test User')
        ->set('email', 'test@example.com')
        ->call('updateProfileInformation');

    $response->assertHasNoErrors();

    $user->refresh();

    expect($user->name)->toEqual('Test User')
        ->and($user->email)->toEqual('test@example.com')
        ->and($user->email_verified_at)->toBeNull();
});

test('email verification status is unchanged when email address is unchanged', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('profile.update');

    $this->actingAs($user);

    $response = Volt::test('settings.profile')
        ->set('name', 'Test User')
        ->set('email', $user->email)
        ->call('updateProfileInformation');

    $response->assertHasNoErrors();

    expect($user->refresh()->email_verified_at)->not->toBeNull();
});

test('user can delete their account', function () {
    $user = User::factory()->create(['avatar' => null]);

    $user->givePermissionTo('profile.delete');

    $this->actingAs($user);

    $response = Volt::test('settings.delete-user-form')
        ->set('password', 'secret')
        ->call('deleteUser');

    $response
        ->assertHasNoErrors()
        ->assertRedirect('/');

    expect($user->fresh())->toBeNull()
        ->and(auth()->check())->toBeFalse();
});

test('correct password must be provided to delete account', function () {
    $user = User::factory()->create(['avatar' => null]);

    $user->givePermissionTo('user.delete');

    $this->actingAs($user);

    $response = Volt::test('settings.delete-user-form')
        ->set('password', 'wrong-password')
        ->call('deleteUser');

    $response->assertHasErrors(['password']);

    expect($user->fresh())->not->toBeNull();
});

test('avatar can be updated in profile', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('profile.update');

    $this->actingAs($user);

    $file = UploadedFile::fake()->image('new-avatar.jpg');

    $response = Volt::test('settings.profile')
        ->set('name', $user->name)
        ->set('email', $user->email)
        ->set('avatar', $file)
        ->call('updateProfileInformation');

    $response->assertHasNoErrors();

    $user->refresh();

    expect($user->avatar)->toContain('/storage/users/');
    Storage::disk('public')->assertExists(str_replace('/storage/', '', $user->avatar));
});

test('old avatar is deleted when new avatar is uploaded', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('profile.update');

    $this->actingAs($user);

    // First, give the user an existing avatar
    $oldFile = UploadedFile::fake()->image('old-avatar.jpg');
    $oldPath = $oldFile->store('users', 'public');
    $user->update(['avatar' => "/storage/{$oldPath}"]);

    // Now upload a new avatar
    $newFile = UploadedFile::fake()->image('new-avatar.jpg');

    $response = Volt::test('settings.profile')
        ->set('name', $user->name)
        ->set('email', $user->email)
        ->set('avatar', $newFile)
        ->call('updateProfileInformation');

    $response->assertHasNoErrors();

    // Old avatar should be deleted
    Storage::disk('public')->assertMissing($oldPath);

    // New avatar should exist
    $user->refresh();
    Storage::disk('public')->assertExists(str_replace('/storage/', '', $user->avatar));
});

test('avatar upload validates file type in profile', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('profile.update');

    $this->actingAs($user);

    $file = UploadedFile::fake()->create('document.pdf', 100);

    $response = Volt::test('settings.profile')
        ->set('name', $user->name)
        ->set('email', $user->email)
        ->set('avatar', $file)
        ->call('updateProfileInformation');

    $response->assertHasErrors(['avatar' => 'image']);
});

test('avatar upload validates file size in profile', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('profile.update');

    $this->actingAs($user);

    $file = UploadedFile::fake()->image('large-avatar.jpg')->size(2048); // 2MB

    $response = Volt::test('settings.profile')
        ->set('name', $user->name)
        ->set('email', $user->email)
        ->set('avatar', $file)
        ->call('updateProfileInformation');

    $response->assertHasErrors(['avatar' => 'max']);
});

test('profile can be updated without uploading new avatar', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('profile.update');

    $this->actingAs($user);

    // Update profile without setting avatar field (simulating form submission without file change)
    $response = Volt::test('settings.profile')
        ->set('name', 'Updated Name')
        ->set('email', 'updated@example.com')
        ->call('updateProfileInformation');

    $response->assertHasNoErrors();

    $user->refresh();

    expect($user->name)->toBe('Updated Name')
        ->and($user->email)->toBe('updated@example.com')
        ->and($user->avatar)->toBeNull();
});

test('avatar field is optional in profile update', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('profile.update');

    $this->actingAs($user);

    $response = Volt::test('settings.profile')
        ->set('name', 'Test Name')
        ->set('email', 'test@example.com')
        ->set('avatar', null)
        ->call('updateProfileInformation');

    $response->assertHasNoErrors();

    $user->refresh();

    expect($user->name)->toBe('Test Name')
        ->and($user->email)->toBe('test@example.com')
        ->and($user->avatar)->toBeNull();
});

test('existing avatar is cleared when avatar field is set to null', function () {
    // This test documents the current behavior where setting avatar to null clears existing avatar
    $user = User::factory()->create(['avatar' => '/storage/users/existing-avatar.jpg']);

    $user->givePermissionTo('profile.update');

    $this->actingAs($user);

    $response = Volt::test('settings.profile')
        ->set('name', 'Updated Name')
        ->set('email', 'updated@example.com')
        ->set('avatar', null)
        ->call('updateProfileInformation');

    $response->assertHasNoErrors();

    $user->refresh();

    expect($user->name)->toBe('Updated Name')
        ->and($user->email)->toBe('updated@example.com')
        ->and($user->avatar)->toBeNull();
});

test('user avatar is deleted from storage when account is deleted', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('profile.update', 'profile.delete');

    $this->actingAs($user);

    // First, upload an avatar
    $file = UploadedFile::fake()->image('user-avatar.jpg');
    Volt::test('settings.profile')
        ->set('name', $user->name)
        ->set('email', $user->email)
        ->set('avatar', $file)
        ->call('updateProfileInformation');

    $user->refresh();
    $avatarPath = str_replace('/storage/', '', $user->avatar);

    // Verify avatar exists in storage
    Storage::disk('public')->assertExists($avatarPath);

    // Delete the user account
    $response = Volt::test('settings.delete-user-form')
        ->set('password', 'secret')
        ->call('deleteUser');

    $response->assertHasNoErrors()
        ->assertRedirect('/');

    // Verify avatar file is deleted from storage
    Storage::disk('public')->assertMissing($avatarPath);

    // Verify user is deleted
    expect($user->fresh())->toBeNull()
        ->and(auth()->check())->toBeFalse();
});

test('user without avatar can be deleted successfully', function () {
    $user = User::factory()->create(['avatar' => null]); // Explicitly set avatar to null

    $user->givePermissionTo('profile.delete');

    $this->actingAs($user);

    $response = Volt::test('settings.delete-user-form')
        ->set('password', 'secret')
        ->call('deleteUser');

    $response->assertHasNoErrors()
        ->assertRedirect('/');

    expect($user->fresh())->toBeNull()
        ->and(auth()->check())->toBeFalse();
});

test('avatar deletion is skipped when user has no avatar during account deletion', function () {
    $user = User::factory()->create(['avatar' => null]);

    $user->givePermissionTo('profile.delete');

    $this->actingAs($user);

    // This test ensures the deletion process doesn't fail when avatar is null
    $response = Volt::test('settings.delete-user-form')
        ->set('password', 'secret')
        ->call('deleteUser');

    $response->assertHasNoErrors()
        ->assertRedirect('/');

    expect($user->fresh())->toBeNull()
        ->and(auth()->check())->toBeFalse();
});

test('users without profile update permission cannot update profile', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $response = Volt::test('settings.profile')
        ->set('name', 'Updated Name')
        ->set('email', 'updated@example.com')
        ->call('updateProfileInformation');

    // Verify profile was not updated
    $user->refresh();
    expect($user->name)->not->toBe('Updated Name')
        ->and($user->email)->not->toBe('updated@example.com');
});

test('users without user delete permission cannot delete their account', function () {
    $user = User::factory()->create([
        'password' => Hash::make('secret'),
    ]);

    $this->actingAs($user);

    $response = Volt::test('settings.delete-user-form')
        ->set('password', 'secret')
        ->call('deleteUser');

    // Verify user was not deleted
    expect($user->fresh())->not->toBeNull()
        ->and(auth()->check())->toBeTrue();
});
