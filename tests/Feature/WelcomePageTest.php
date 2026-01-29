<?php

declare(strict_types=1);

use App\Models\User;

test('welcome page renders successfully for guests', function () {
    $response = $this->get('/');

    $response->assertSuccessful();
    $response->assertSee('Log in');
    $response->assertSee('Register');
    $response->assertDontSee('Dashboard');
});

test('welcome page shows dashboard link for authenticated users', function () {
    $user = User::factory()->active()->create();
    $user->givePermissionTo('user.login');

    $this->actingAs($user);

    $response = $this->get('/');

    $response->assertSuccessful();
    $response->assertSee('Dashboard');
    $response->assertDontSee('Log in');
    $response->assertDontSee('Register');
});

test('welcome page displays app name', function () {
    $response = $this->get('/');

    $response->assertSuccessful();
    $response->assertSee(config('app.name'));
});
