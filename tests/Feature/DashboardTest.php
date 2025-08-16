<?php

declare(strict_types=1);

use App\Models\User;

test('guests are redirected to the login page', function () {
    $this->get('/dashboard')->assertRedirect('/login');
});

test('authenticated users can visit the dashboard', function () {
    $user = User::factory()->create();
    $user->givePermissionTo(['user.login', 'dashboard.view']);
    $this->actingAs($user);

    $this->get('/dashboard')->assertSuccessful();
});
