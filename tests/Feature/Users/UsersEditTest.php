<?php

declare(strict_types=1);

use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

beforeEach(function () {
    $this->user = User::factory()->active()->create();
    $this->user->assignRole('user-manager', 'user');
    $this->targetUser = User::factory()->active()->create([
        'name' => 'Target User',
        'email' => 'target@example.com',
    ]);
    $this->actingAs($this->user);
    Storage::fake('public');
});

test('users edit page loads successfully', function () {
    $response = $this->get(route('users.edit', $this->targetUser));

    $response->assertStatus(200);
    $response->assertSee(__('Update') . ' - ' . $this->targetUser->name);
});

test('edit form is pre-filled with user data', function () {
    Livewire::test('pages.users.edit', ['user' => $this->targetUser])
        ->assertSet('name', $this->targetUser->name)
        ->assertSet('email', $this->targetUser->email)
        ->assertSet('status', $this->targetUser->status->value);
});

test('user can be updated successfully', function () {
    Livewire::test('pages.users.edit', ['user' => $this->targetUser])
        ->set('name', 'Updated Name')
        ->set('email', 'updated@example.com')
        ->set('status', UserStatus::INACTIVE->value)
        ->call('save')
        ->assertHasNoErrors()
        ->assertRedirect(route('users.index'));

    $this->targetUser->refresh();

    expect($this->targetUser->name)->toBe('Updated Name')
        ->and($this->targetUser->email)
        ->toBe('updated@example.com')
        ->and($this->targetUser->status)
        ->toBe(UserStatus::INACTIVE);
});

test('user update validates required fields', function () {
    Livewire::test('pages.users.edit', ['user' => $this->targetUser])
        ->set('name', '')
        ->set('email', '')
        ->call('save')
        ->assertHasErrors(['name' => 'required'])
        ->assertHasErrors(['email' => 'required']);
});

test('user update validates email format', function () {
    Livewire::test('pages.users.edit', ['user' => $this->targetUser])
        ->set('email', 'invalid-email')
        ->call('save')
        ->assertHasErrors(['email' => 'email']);
});

test('user update validates email uniqueness except for current user', function () {
    $anotherUser = User::factory()->create(['email' => 'another@example.com']);

    // Should fail when trying to use another user's email
    Livewire::test('pages.users.edit', ['user' => $this->targetUser])
        ->set('email', 'another@example.com')
        ->call('save')
        ->assertHasErrors(['email' => 'unique']);

    // Should pass when keeping the same email
    Livewire::test('pages.users.edit', ['user' => $this->targetUser])
        ->set('email', $this->targetUser->email)
        ->call('save')
        ->assertHasNoErrors(['email']);
});

test('user update validates name length', function () {
    Livewire::test('pages.users.edit', ['user' => $this->targetUser])
        ->set('name', str_repeat('a', 256))
        ->call('save')
        ->assertHasErrors(['name' => 'max']);
});

test('user update validates email length', function () {
    Livewire::test('pages.users.edit', ['user' => $this->targetUser])
        ->set('email', str_repeat('a', 250) . '@example.com')
        ->call('save')
        ->assertHasErrors(['email' => 'max']);
});

test('avatar can be updated', function () {
    $file = UploadedFile::fake()->image('new-avatar.jpg');

    Livewire::test('pages.users.edit', ['user' => $this->targetUser])
        ->set('avatar', $file)
        ->call('save')
        ->assertHasNoErrors();

    $this->targetUser->refresh();

    expect($this->targetUser->avatar)->toContain('/storage/users/');
    Storage::disk('public')->assertExists(str_replace('/storage/', '', $this->targetUser->avatar));
});

test('old avatar is deleted when new one is uploaded', function () {
    // First, give the user an existing avatar
    $oldFile = UploadedFile::fake()->image('old-avatar.jpg');
    $oldPath = $oldFile->store('users', 'public');
    $this->targetUser->update(['avatar' => "/storage/{$oldPath}"]);

    // Now upload a new avatar
    $newFile = UploadedFile::fake()->image('new-avatar.jpg');

    Livewire::test('pages.users.edit', ['user' => $this->targetUser])
        ->set('avatar', $newFile)
        ->call('save');

    // Old avatar should be deleted
    Storage::disk('public')->assertMissing($oldPath);

    // New avatar should exist
    $this->targetUser->refresh();
    Storage::disk('public')->assertExists(str_replace('/storage/', '', $this->targetUser->avatar));
});

test('avatar upload validates file type', function () {
    $file = UploadedFile::fake()->create('document.pdf', 100);

    Livewire::test('pages.users.edit', ['user' => $this->targetUser])
        ->set('avatar', $file)
        ->call('save')
        ->assertHasErrors(['avatar' => 'image']);
});

test('avatar upload validates file size', function () {
    $file = UploadedFile::fake()->image('avatar.jpg')->size(2048); // 2MB

    Livewire::test('pages.users.edit', ['user' => $this->targetUser])
        ->set('avatar', $file)
        ->call('save')
        ->assertHasErrors(['avatar' => 'max']);
});

test('status options are available', function () {
    $component = Livewire::test('pages.users.edit', ['user' => $this->targetUser]);

    expect($component->get('statusOptions'))->toBe(UserStatus::all());
});

test('user status is displayed correctly in indicator', function () {
    $activeUser = User::factory()->create(['status' => UserStatus::ACTIVE]);
    $inactiveUser = User::factory()->create(['status' => UserStatus::INACTIVE]);
    $suspendedUser = User::factory()->create(['status' => UserStatus::SUSPENDED]);

    $activeResponse = $this->get(route('users.edit', $activeUser));
    $inactiveResponse = $this->get(route('users.edit', $inactiveUser));
    $suspendedResponse = $this->get(route('users.edit', $suspendedUser));

    $activeResponse->assertSee('status-success');
    $inactiveResponse->assertSee('status-warning');
    $suspendedResponse->assertSee('status-error');
});

test('user can update status', function () {
    Livewire::test('pages.users.edit', ['user' => $this->targetUser])
        ->set('status', UserStatus::SUSPENDED->value)
        ->call('save')
        ->assertHasNoErrors();

    $this->targetUser->refresh();

    expect($this->targetUser->status)->toBe(UserStatus::SUSPENDED);
});

test('user cannot be updated because of insufficient permissions', function () {
    $this->user->removeRole('user-manager');

    Livewire::test('pages.users.edit')
        ->set('name', 'John Doe')
        ->set('email', 'john@example.com')
        ->set('status', UserStatus::ACTIVE->value)
        ->call('save')
        ->assertForbidden();
})->skip('This test is not working because assertForbidden() is not working when using $stopPropagationOnFailure');
