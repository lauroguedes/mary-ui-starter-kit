<?php

declare(strict_types=1);

use App\Enums\UserStatus;
use App\Models\User;
use App\Notifications\UserCreated;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;

use function Pest\Livewire\livewire;

beforeEach(function () {
    $this->user = User::factory()->active()->create();
    $this->user->assignRole('user-manager', 'user');
    $this->actingAs($this->user);
    Storage::fake('public');
    Notification::fake();
});

test('users create page loads successfully', function () {
    $response = $this->get(route('users.create'));

    $response->assertSuccessful();
    $response->assertSee(__('Create User'));
});

test('user can be created successfully', function () {
    livewire('pages.users.create')
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
    livewire('pages.users.create')
        ->set('name', 'John Doe')
        ->set('email', 'john@example.com')
        ->set('status', UserStatus::ACTIVE->value)
        ->call('save');

    $user = User::where('email', 'john@example.com')->first();

    Notification::assertSentTo($user, UserCreated::class);
});

test('user creation validates required fields', function (string $field, string $rule) {
    livewire('pages.users.create')
        ->call('save')
        ->assertHasErrors([$field => $rule]);
})->with('required_fields');

test('user creation validates email format', function () {
    livewire('pages.users.create')
        ->set('name', 'John Doe')
        ->set('email', 'invalid-email')
        ->set('status', UserStatus::ACTIVE->value)
        ->call('save')
        ->assertHasErrors(['email' => 'email']);
});

test('user creation validates email uniqueness', function () {
    User::factory()->create(['email' => 'existing@example.com']);

    livewire('pages.users.create')
        ->set('name', 'John Doe')
        ->set('email', 'existing@example.com')
        ->set('status', UserStatus::ACTIVE->value)
        ->call('save')
        ->assertHasErrors(['email' => 'unique']);
});

test('user creation validates max length', function (string $field, int $maxLength) {
    $value = $field === 'email'
        ? str_repeat('a', $maxLength - 10) . '@example.com'
        : str_repeat('a', $maxLength + 1);

    livewire('pages.users.create')
        ->set('name', $field === 'name' ? $value : 'John Doe')
        ->set('email', $field === 'email' ? $value : 'john@example.com')
        ->set('status', UserStatus::ACTIVE->value)
        ->call('save')
        ->assertHasErrors([$field => 'max']);
})->with([
    'name exceeds 100 chars' => ['name', 100],
    'email exceeds 255 chars' => ['email', 255],
]);

test('avatar can be uploaded during user creation', function () {
    $file = UploadedFile::fake()->image('avatar.jpg');

    livewire('pages.users.create')
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

    livewire('pages.users.create')
        ->set('name', 'John Doe')
        ->set('email', 'john@example.com')
        ->set('status', UserStatus::ACTIVE->value)
        ->set('avatar', $file)
        ->call('save')
        ->assertHasErrors(['avatar' => 'image']);
});

test('avatar upload validates file size', function () {
    $file = UploadedFile::fake()->image('avatar.jpg')->size(2048); // 2MB

    livewire('pages.users.create')
        ->set('name', 'John Doe')
        ->set('email', 'john@example.com')
        ->set('status', UserStatus::ACTIVE->value)
        ->set('avatar', $file)
        ->call('save')
        ->assertHasErrors(['avatar' => 'max']);
});

test('default status is active', function () {
    $component = livewire('pages.users.create');

    expect($component->get('status'))->toBe(UserStatus::ACTIVE->value);
});

test('status options are available', function () {
    $component = livewire('pages.users.create');

    expect($component->get('statusOptions'))->toBe(UserStatus::all());
});

test('password is auto-generated and hashed', function () {
    livewire('pages.users.create')
        ->set('name', 'John Doe')
        ->set('email', 'john@example.com')
        ->set('status', UserStatus::ACTIVE->value)
        ->call('save');

    $user = User::where('email', 'john@example.com')->first();

    expect($user->password)->not()->toBeEmpty()
        ->and($user->password)->not()
        ->toBe('password');
});
