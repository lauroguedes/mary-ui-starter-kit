<?php

declare(strict_types=1);

use App\Enums\SocialiteProviders;

beforeEach(function () {
    $this->provider = SocialiteProviders::GOOGLE;
});

test('redirect route exists and is accessible', function () {
    $response = $this->get("/oauth/{$this->provider->value}/redirect");

    $response->assertStatus(302);
});

test('callback route handles errors gracefully', function () {
    $response = $this->get("/oauth/{$this->provider->value}/callback");

    $response->assertRedirect(route('login'));
    $response->assertSessionHas('error');
});

test('invalid provider redirects to login', function () {
    $response = $this->get('/oauth/invalid-provider/redirect');

    $response->assertNotFound();
});

test('invalid provider callback redirects to login', function () {
    $response = $this->get('/oauth/invalid-provider/callback');

    $response->assertNotFound();
});
