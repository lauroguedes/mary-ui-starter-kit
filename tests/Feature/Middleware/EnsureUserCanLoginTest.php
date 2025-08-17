<?php

declare(strict_types=1);

use App\Models\User;

describe('EnsureUserCanLogin Middleware', function () {
    test('active user with user.login permission can access protected routes', function () {
        $user = User::factory()->active()->create();
        $this->actingAs($user);

        $this->get(route('dashboard'))
            ->assertSuccessful();
    });

    test('suspended user is logged out and redirected', function () {
        $user = User::factory()->suspended()->create();
        $this->actingAs($user);

        $this->get(route('dashboard'))
            ->assertRedirect('/');

        $this->assertGuest();
    });

    test('user without user.login permission is logged out and redirected', function () {
        $user = User::factory()->active()->create();
        // Remove the user role which contains user.login permission
        $user->removeRole('user');
        $this->actingAs($user);

        $this->get(route('dashboard'))
            ->assertRedirect('/');

        $this->assertGuest();
    });

    test('inactive user with user.login permission can still access routes', function () {
        $user = User::factory()->inactive()->create();
        $this->actingAs($user);

        $this->get(route('dashboard'))
            ->assertSuccessful();
    });

    test('middleware shows correct error message when blocking login', function () {
        $user = User::factory()->suspended()->create();
        $this->actingAs($user);

        $this->get(route('dashboard'))
            ->assertRedirect('/')
            ->assertSessionHasErrors(['email' => __('User cannot log in.')]);
    });
});
