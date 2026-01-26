<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

describe('ResetDemoDataCommand', function () {
    test('command fails when demo mode is disabled and no force flag', function () {
        config()->set('app.demo.enabled', false);

        $this->artisan('demo:reset')
            ->assertFailed()
            ->expectsOutput('Demo mode is not enabled. Use --force to override.');
    });

    test('command succeeds when demo mode is enabled', function () {
        config()->set('app.demo.enabled', true);

        User::factory()->active()->create(['email' => 'existing@user.com']);

        $this->artisan('demo:reset')
            ->assertSuccessful()
            ->expectsOutput('Demo data has been reset successfully.');

        $this->assertDatabaseMissing('users', ['email' => 'existing@user.com']);
        $this->assertDatabaseHas('users', ['email' => 'admin@user.com']);
    });

    test('command succeeds with force flag regardless of demo mode', function () {
        config()->set('app.demo.enabled', false);

        User::factory()->active()->create(['email' => 'temp@user.com']);

        $this->artisan('demo:reset --force')
            ->assertSuccessful()
            ->expectsOutput('Demo data has been reset successfully.');

        $this->assertDatabaseMissing('users', ['email' => 'temp@user.com']);
        $this->assertDatabaseHas('users', ['email' => 'admin@user.com']);
    });

    test('command clears all relevant tables and re-seeds', function () {
        config()->set('app.demo.enabled', false);

        $this->artisan('demo:reset --force')->assertSuccessful();

        expect(User::count())->toBeGreaterThan(0);
        expect(Role::count())->toBeGreaterThan(0);
    });

    test('super-admin user is not created when demo mode is enabled', function () {
        config()->set('app.demo.enabled', true);

        $this->artisan('demo:reset')
            ->assertSuccessful();

        $this->assertDatabaseMissing('users', ['email' => 'super-admin@user.com']);
        $this->assertDatabaseHas('users', ['email' => 'admin@user.com']);
    });

    test('super-admin user is created when demo mode is disabled', function () {
        config()->set('app.demo.enabled', false);

        $this->artisan('demo:reset --force')
            ->assertSuccessful();

        $this->assertDatabaseHas('users', ['email' => 'super-admin@user.com']);
    });

    test('demo password is rotated on each reset', function () {
        config()->set('app.demo.enabled', true);
        cache()->put('demo-password', 'old-password');

        $this->artisan('demo:reset')->assertSuccessful();

        expect(cache('demo-password'))->not->toBe('old-password');
    });

    test('only admin user gets the rotated demo password', function () {
        config()->set('app.demo.enabled', true);

        $this->artisan('demo:reset')->assertSuccessful();

        $rotatedPassword = cache('demo-password');

        $adminUser = User::whereEmail('admin@user.com')->first();
        $regularUser = User::whereEmail('user@user.com')->first();

        expect(Hash::check($rotatedPassword, $adminUser->password))->toBeTrue();
        expect(Hash::check($rotatedPassword, $regularUser->password))->toBeFalse();
    });
});
