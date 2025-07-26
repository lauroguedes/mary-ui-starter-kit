<?php

declare(strict_types=1);

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Livewire\Volt\Volt as LivewireVolt;

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
});

test('login screen can be rendered', function () {
    $response = $this->get('/login');

    $response->assertStatus(200);
});

test('users can authenticate using the login screen', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('user.login', 'dashboard.view');

    $response = LivewireVolt::test('auth.login')
        ->set('email', $user->email)
        ->set('password', 'secret')
        ->call('login');

    $response
        ->assertHasNoErrors()
        ->assertRedirect(route('dashboard', absolute: false));

    $this->assertAuthenticated();
});

test('users can not authenticate with invalid password', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('user.login');

    $response = LivewireVolt::test('auth.login')
        ->set('email', $user->email)
        ->set('password', 'wrong-password')
        ->call('login');

    $response->assertHasErrors('email');

    $this->assertGuest();
});

test('users can logout', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('user.login');

    $response = $this->actingAs($user)->post('/logout');

    $response->assertRedirect('/');

    $this->assertGuest();
});

test('users without login permission cannot authenticate', function () {
    $user = User::factory()->create();

    $response = LivewireVolt::test('auth.login')
        ->set('email', $user->email)
        ->set('password', 'secret')
        ->call('login');

    $response->assertHasErrors('email');

    $errors = $response->errors();
    $this->assertTrue($errors->has('email'));
    $this->assertEquals(__('User cannot log in.'), $errors->first('email'));
    $this->assertGuest();
});

test('users without dashboard view permission are redirected to profile', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('user.login');

    $response = LivewireVolt::test('auth.login')
        ->set('email', $user->email)
        ->set('password', 'secret')
        ->call('login');

    $response
        ->assertHasNoErrors()
        ->assertRedirect(route('settings.profile', absolute: false));

    $this->assertAuthenticated();
});
