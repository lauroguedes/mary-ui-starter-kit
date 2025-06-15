<?php

declare(strict_types=1);

use App\Models\User;
use Livewire\Volt\Volt;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('public');
});

test('profile page is displayed', function () {
    $this->actingAs($user = User::factory()->create());

    $this->get('/settings/profile')->assertOk();
});

test('profile information can be updated', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $response = Volt::test('settings.profile')
        ->set('name', 'Test User')
        ->set('email', 'test@example.com')
        ->call('updateProfileInformation');

    $response->assertHasNoErrors();

    $user->refresh();

    expect($user->name)->toEqual('Test User');
    expect($user->email)->toEqual('test@example.com');
    expect($user->email_verified_at)->toBeNull();
});

test('email verification status is unchanged when email address is unchanged', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $response = Volt::test('settings.profile')
        ->set('name', 'Test User')
        ->set('email', $user->email)
        ->call('updateProfileInformation');

    $response->assertHasNoErrors();

    expect($user->refresh()->email_verified_at)->not->toBeNull();
});

test('user can delete their account', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $response = Volt::test('settings.delete-user-form')
        ->set('password', 'secret')
        ->call('deleteUser');

    $response
        ->assertHasNoErrors()
        ->assertRedirect('/');

    expect($user->fresh())->toBeNull();
    expect(auth()->check())->toBeFalse();
});

test('correct password must be provided to delete account', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $response = Volt::test('settings.delete-user-form')
        ->set('password', 'wrong-password')
        ->call('deleteUser');

    $response->assertHasErrors(['password']);

    expect($user->fresh())->not->toBeNull();
});

test('avatar can be updated in profile', function () {
    $user = User::factory()->create();
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
    $this->actingAs($user);
    
    // Update profile without setting avatar field (simulating form submission without file change)
    $response = Volt::test('settings.profile')
        ->set('name', 'Updated Name')
        ->set('email', 'updated@example.com')
        ->call('updateProfileInformation');
        
    $response->assertHasNoErrors();
    
    $user->refresh();
    
    expect($user->name)->toBe('Updated Name');
    expect($user->email)->toBe('updated@example.com');
    // Avatar remains null as no file was uploaded and user had no previous avatar
    expect($user->avatar)->toBeNull();
});

test('avatar field is optional in profile update', function () {
    $user = User::factory()->create();
    $this->actingAs($user);
    
    $response = Volt::test('settings.profile')
        ->set('name', 'Test Name')  
        ->set('email', 'test@example.com')
        ->set('avatar', null)
        ->call('updateProfileInformation');
        
    $response->assertHasNoErrors();
    
    $user->refresh();
    
    expect($user->name)->toBe('Test Name');
    expect($user->email)->toBe('test@example.com');
    expect($user->avatar)->toBeNull();
});

test('existing avatar is cleared when avatar field is set to null', function () {
    // This test documents the current behavior where setting avatar to null clears existing avatar
    $user = User::factory()->create(['avatar' => '/storage/users/existing-avatar.jpg']);
    $this->actingAs($user);
    
    $response = Volt::test('settings.profile')
        ->set('name', 'Updated Name')
        ->set('email', 'updated@example.com')
        ->set('avatar', null)
        ->call('updateProfileInformation');
        
    $response->assertHasNoErrors();
    
    $user->refresh();
    
    expect($user->name)->toBe('Updated Name');
    expect($user->email)->toBe('updated@example.com');
    expect($user->avatar)->toBeNull(); // Current behavior: avatar is cleared
});
