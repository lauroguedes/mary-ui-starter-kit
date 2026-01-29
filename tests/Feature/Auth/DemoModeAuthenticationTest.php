<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Support\Facades\Gate;

use function Pest\Livewire\livewire;

describe('Demo Mode Authentication', function () {
    test('super-admin cannot access protected routes in demo mode', function () {
        config()->set('app.demo.enabled', true);

        $superAdmin = User::factory()->active()->create(['email' => 'super-admin@test.com']);
        $superAdmin->assignRole('super-admin');

        $this->actingAs($superAdmin);

        $this->get(route('dashboard'))
            ->assertRedirect('/')
            ->assertSessionHasErrors(['email' => __('Super-admin login is disabled in demo mode.')]);

        $this->assertGuest();
    });

    test('super-admin can login when demo mode is disabled', function () {
        config()->set('app.demo.enabled', false);

        $superAdmin = User::factory()->active()->create(['email' => 'super-admin@test.com']);
        $superAdmin->assignRole('super-admin');

        $this->actingAs($superAdmin);

        $this->get(route('dashboard'))
            ->assertSuccessful();

        $this->assertAuthenticated();
    });

    test('admin can login in demo mode', function () {
        config()->set('app.demo.enabled', true);

        $adminUser = User::factory()->active()->create(['email' => 'admin@test.com']);
        $adminUser->assignRole('admin');

        $this->actingAs($adminUser);

        $this->get(route('dashboard'))
            ->assertSuccessful();

        $this->assertAuthenticated();
    });

    test('gate bypass is disabled for super-admin in demo mode', function () {
        config()->set('app.demo.enabled', true);

        // Re-boot the service provider to pick up the config change
        app()->register(App\Providers\AppServiceProvider::class, true);

        $superAdmin = User::factory()->active()->create(['email' => 'super-admin@test.com']);
        $superAdmin->assignRole('super-admin');

        // In demo mode, the Gate::before bypass should not be registered,
        // so super-admin should NOT automatically pass all gate checks
        $result = Gate::allows('some-nonexistent-ability');

        expect($result)->toBeFalse();
    });

    test('login form pre-fills admin credentials in demo mode', function () {
        config()->set('app.demo.enabled', true);
        cache()->put('demo-password', 'demo-test-pass');

        $component = livewire('pages::auth.login');

        expect($component->get('email'))->toBe('admin@user.com');
        expect($component->get('password'))->toBe('demo-test-pass');
    });

    test('login form does not pre-fill credentials when demo mode is disabled', function () {
        config()->set('app.demo.enabled', false);

        $component = livewire('pages::auth.login');

        expect($component->get('email'))->toBe('');
        expect($component->get('password'))->toBe('');
    });

    test('google login button is hidden and message is shown in demo mode', function () {
        config()->set('app.demo.enabled', true);

        $this->get('/login')
            ->assertSuccessful()
            ->assertDontSee('Login with Google')
            ->assertSee('Social login is disabled in demo mode.');
    });

    test('google login button is visible when demo mode is disabled', function () {
        config()->set('app.demo.enabled', false);

        $this->get('/login')
            ->assertSuccessful()
            ->assertSee('Login with Google');
    });

    test('gate bypass is active for super-admin when demo mode is disabled', function () {
        config()->set('app.demo.enabled', false);

        // Re-boot the service provider to pick up the config change
        app()->register(App\Providers\AppServiceProvider::class, true);

        $superAdmin = User::factory()->active()->create(['email' => 'super-admin@test.com']);
        $superAdmin->assignRole('super-admin');

        $this->actingAs($superAdmin);

        $result = Gate::allows('some-nonexistent-ability');

        expect($result)->toBeTrue();
    });
});
