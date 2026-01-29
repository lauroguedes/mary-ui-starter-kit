<?php

declare(strict_types=1);

use App\Models\User;

use function Pest\Livewire\livewire;

test('login screen can be rendered')
    ->get('/login')
    ->assertSuccessful();

test('users can authenticate using the login screen', function () {
    $user = User::factory()->create();

    livewire('pages::auth.login')
        ->set('email', $user->email)
        ->set('password', 'secret')
        ->call('login')
        ->assertHasNoErrors()
        ->assertRedirect(route('dashboard', absolute: false));

    $this->assertAuthenticated();
});

test('users can not authenticate with invalid password', function () {
    $user = User::factory()->create();

    livewire('pages::auth.login')
        ->set('email', $user->email)
        ->set('password', 'wrong-password')
        ->call('login')
        ->assertHasErrors('email');

    $this->assertGuest();
});

test('users can logout', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post('/logout')
        ->assertRedirect('/');

    $this->assertGuest();
});

test('users without login permission cannot authenticate', function () {
    $user = User::factory()->create();
    $user->removeRole('user');

    livewire('pages::auth.login')
        ->set('email', $user->email)
        ->set('password', 'secret')
        ->call('login')
        ->assertRedirectToRoute('settings.profile');

    $this->get(route('settings.profile'))
        ->assertRedirect('/')
        ->assertInvalid(['email' => __('User cannot log in.')]);

    $this->assertGuest();
});

test('users without dashboard view permission are redirected to profile', function () {
    $user = User::factory()->create();
    $user->removeRole('user');
    $user->givePermissionTo('user.login');

    livewire('pages::auth.login')
        ->set('email', $user->email)
        ->set('password', 'secret')
        ->call('login')
        ->assertHasNoErrors()
        ->assertRedirect(route('settings.profile', absolute: false));

    $this->assertAuthenticated();
});
