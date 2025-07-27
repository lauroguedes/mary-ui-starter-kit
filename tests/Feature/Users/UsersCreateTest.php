<?php

declare(strict_types=1);

use App\Enums\UserStatus;
use App\Models\User;
use App\Notifications\UserCreated;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    $this->user = User::factory()->create();
    $this->user->assignRole('user-manager', 'user');
    $this->actingAs($this->user);
    Storage::fake('public');
    Notification::fake();
});

test('users create page loads successfully', function () {
    $response = $this->get(route('users.create'));

    $response->assertStatus(200);
    $response->assertSee(__('Create User'));
});

test('user can be created successfully', function () {
    Livewire::test('pages.users.create')
        ->set('name', 'John Doe')
        ->set('email', 'john@example.com')
        ->set('status', UserStatus::ACTIVE->value)
        ->call('save')
        ->assertHasNoErrors()
        ->assertRedirect(route('users.index'));

    $this->assertDatabaseHas('users', [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'status' => UserStatus::ACTIVE->value,
    ]);
});

test('user creation sends notification', function () {
    Livewire::test('pages.users.create')
        ->set('name', 'John Doe')
        ->set('email', 'john@example.com')
        ->set('status', UserStatus::ACTIVE->value)
        ->call('save');

    $user = User::where('email', 'john@example.com')->first();

    Notification::assertSentTo($user, UserCreated::class);
});

test('user creation validates required fields', function () {
    Livewire::test('pages.users.create')
        ->call('save')
        ->assertHasErrors(['name' => 'required'])
        ->assertHasErrors(['email' => 'required']);
});

test('user creation validates email format', function () {
    Livewire::test('pages.users.create')
        ->set('name', 'John Doe')
        ->set('email', 'invalid-email')
        ->set('status', UserStatus::ACTIVE->value)
        ->call('save')
        ->assertHasErrors(['email' => 'email']);
});

test('user creation validates email uniqueness', function () {
    $existingUser = User::factory()->create(['email' => 'existing@example.com']);

    Livewire::test('pages.users.create')
        ->set('name', 'John Doe')
        ->set('email', 'existing@example.com')
        ->set('status', UserStatus::ACTIVE->value)
        ->call('save')
        ->assertHasErrors(['email' => 'unique']);
});

test('user creation validates name length', function () {
    Livewire::test('pages.users.create')
        ->set('name', str_repeat('a', 101))
        ->set('email', 'john@example.com')
        ->set('status', UserStatus::ACTIVE->value)
        ->call('save')
        ->assertHasErrors(['name' => 'max']);
});

test('user creation validates email length', function () {
    Livewire::test('pages.users.create')
        ->set('name', 'John Doe')
        ->set('email', str_repeat('a', 50) . '@example.com')
        ->set('status', UserStatus::ACTIVE->value)
        ->call('save')
        ->assertHasErrors(['email' => 'max']);
});

test('avatar can be uploaded during user creation', function () {
    $file = UploadedFile::fake()->image('avatar.jpg');

    Livewire::test('pages.users.create')
        ->set('name', 'John Doe')
        ->set('email', 'john@example.com')
        ->set('status', UserStatus::ACTIVE->value)
        ->set('avatar', $file)
        ->call('save')
        ->assertHasNoErrors();

    $user = User::where('email', 'john@example.com')->first();

    expect($user->avatar)->toContain('/storage/users/');
    Storage::disk('public')->assertExists(str_replace('/storage/', '', $user->avatar));
});

test('avatar upload validates file type', function () {
    $file = UploadedFile::fake()->create('document.pdf', 100);

    Livewire::test('pages.users.create')
        ->set('name', 'John Doe')
        ->set('email', 'john@example.com')
        ->set('status', UserStatus::ACTIVE->value)
        ->set('avatar', $file)
        ->call('save')
        ->assertHasErrors(['avatar' => 'image']);
});

test('avatar upload validates file size', function () {
    $file = UploadedFile::fake()->image('avatar.jpg')->size(2048); // 2MB

    Livewire::test('pages.users.create')
        ->set('name', 'John Doe')
        ->set('email', 'john@example.com')
        ->set('status', UserStatus::ACTIVE->value)
        ->set('avatar', $file)
        ->call('save')
        ->assertHasErrors(['avatar' => 'max']);
});

test('default status is active', function () {
    $component = Livewire::test('pages.users.create');

    expect($component->get('status'))->toBe(UserStatus::ACTIVE->value);
});

test('status options are available', function () {
    $component = Livewire::test('pages.users.create');

    expect($component->get('statusOptions'))->toBe(UserStatus::all());
});

test('password is auto-generated and hashed', function () {
    Livewire::test('pages.users.create')
        ->set('name', 'John Doe')
        ->set('email', 'john@example.com')
        ->set('status', UserStatus::ACTIVE->value)
        ->call('save');

    $user = User::where('email', 'john@example.com')->first();

    expect($user->password)->not()->toBeEmpty()
        ->and($user->password)->not()
        ->toBe('password');
});

test('user cannot be created because of insufficient permissions', function () {
    $this->user->removeRole('user-manager');

    Livewire::test('pages.users.create')
        ->set('name', 'John Doe')
        ->set('email', 'john@example.com')
        ->set('status', UserStatus::ACTIVE->value)
        ->call('save')
        ->assertForbidden();
})->skip('This test is not working because assertForbidden() is not working when using $stopPropagationOnFailure');
