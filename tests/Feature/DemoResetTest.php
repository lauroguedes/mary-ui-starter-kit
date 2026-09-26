<?php

declare(strict_types=1);

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use LauroGuedes\DemoMode\Facades\Demo;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/*
 * The reset command itself belongs to lauroguedes/laravel-demo-mode, which tests
 * it against a real database of its own. What is tested here is the part this
 * project decides: who the seeder creates on a demo, and which of them gets the
 * password the demo publishes.
 *
 * At the seeder rather than through "demo:reset", because the reset runs
 * migrate:fresh and SQLite cannot VACUUM inside the transaction RefreshDatabase
 * holds open. Seeding into an emptied database is the same question without the
 * fight.
 */
function reseed(): void
{
    User::query()->delete();
    DB::table('role_has_permissions')->delete();
    Role::query()->delete();
    Permission::query()->delete();

    app()[PermissionRegistrar::class]->forgetCachedPermissions();

    (new DatabaseSeeder)->setContainer(app())->run();
}

describe('on a public demonstration', function () {
    beforeEach(function () {
        Storage::fake('local');
        config()->set('demo.enabled', true);
    });

    test('the published account is the one the seeder gives a rotating password', function () {
        /* The order a reset uses: staged first, so the seeder hashes what the
         * login page will show. */
        Demo::rotate();

        reseed();

        $credentials = Demo::credentials();
        $admin = User::whereEmail('admin@user.com')->first();

        expect($credentials['email'])->toBe('admin@user.com')
            ->and(Hash::check($credentials['password'], $admin->password))->toBeTrue();
    });

    /*
     * Only the published account rotates. The other fifty keep the factory's
     * password, which is what makes rotating the published one worth anything.
     */
    test('every other account keeps the shared password', function () {
        Demo::rotate();

        reseed();

        expect(Hash::check('secret', User::whereEmail('user@user.com')->first()->password))->toBeTrue()
            ->and(Hash::check('secret', User::whereEmail('admin@user.com')->first()->password))->toBeFalse();
    });

    /*
     * A demo is administered by whoever walked in, so the account that could
     * rewrite the roles and permissions is not created at all.
     */
    test('super-admin is not created', function () {
        reseed();

        $this->assertDatabaseMissing('users', ['email' => 'super-admin@user.com']);
        $this->assertDatabaseHas('users', ['email' => 'admin@user.com']);
    });

    test('the password a visitor wrote down stops working at the next reset', function () {
        Demo::rotate();
        reseed();
        $first = Demo::credentials()['password'];

        Demo::rotate();
        reseed();

        expect(Demo::credentials()['password'])->not->toBe($first)
            ->and(Hash::check($first, User::whereEmail('admin@user.com')->first()->password))->toBeFalse();
    });
});

test('an ordinary installation seeds super-admin and publishes nothing', function () {
    Storage::fake('local');
    config()->set('demo.enabled', false);

    reseed();

    $this->assertDatabaseHas('users', ['email' => 'super-admin@user.com']);

    expect(Demo::credentials())->toBeNull()
        ->and(Hash::check('secret', User::whereEmail('admin@user.com')->first()->password))->toBeTrue();
});

/*
 * The banner is the only part of this a visitor is promised. It used to be a
 * hardcoded "the data will reset every 24 hours" while DEMO_RESET_SCHEDULE
 * defaulted to hourly, which is the exact thing deriving it from the schedule
 * prevents.
 */
test('the banner appears on a demo and says when the reset is', function () {
    config()->set('demo.enabled', true);
    config()->set('demo.reset.schedule', 'hourly');

    $user = User::factory()->active()->create();
    $user->assignRole('user');

    $this->actingAs($user)->get(route('dashboard'))
        ->assertSuccessful()
        ->assertSee('hour', escape: false);
});

test('the banner is absent on an ordinary installation', function () {
    config()->set('demo.enabled', false);

    $user = User::factory()->active()->create();
    $user->assignRole('user');

    $this->actingAs($user)->get(route('dashboard'))
        ->assertSuccessful()
        ->assertDontSee('resets', escape: false);
});
