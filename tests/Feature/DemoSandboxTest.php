<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use LauroGuedes\DemoMode\Doctor\Doctor;
use LauroGuedes\DemoMode\Doctor\Finding;
use LauroGuedes\DemoMode\Sandbox\Sandbox;

/*
 * Scoped isolation is tested against its own database inside
 * lauroguedes/laravel-demo-mode. What is tested here is the part this project
 * decides: that App\Models\User is actually set up for it, and that the Users
 * screen — the list this feature exists for — is scoped when a visitor loads it.
 *
 * The driver is forced on rather than read from the environment, because
 * phpunit.xml pins DEMO_MODE=false so the rest of the suite runs as an ordinary
 * installation. The trait registers its global scope while the model boots and
 * asks the driver once, so the model has to be un-booted after the config changes
 * and again afterwards — otherwise the scope leaks into every test that follows in
 * the same process.
 */
beforeEach(function (): void {
    config()->set('demo.enabled', true);
    config()->set('demo.sandbox.driver', 'scoped');

    User::clearBootedModels();
});

afterEach(function (): void {
    config()->set('demo.enabled', false);
    config()->set('demo.sandbox.driver', 'shared');

    User::clearBootedModels();
});

function sandbox(): string
{
    $id = (string) Str::ulid();

    Sandbox::query()->create(['id' => $id, 'expires_at' => now()->addHour()]);

    return $id;
}

/**
 * Planted rather than created through the screen, because creating one needs a
 * session that already holds the other visitor's sandbox. What is being asserted
 * is the read side.
 */
function userIn(?string $sandbox, string $name): User
{
    $user = User::factory()->active()->create([
        'name' => $name,
        'email' => Str::slug($name) . '@example.test',
    ]);

    DB::table('users')->where('id', $user->id)->update([Sandbox::COLUMN => $sandbox]);

    return $user;
}

/*
 * The silent failure mode, and the only one: a model listed as sandboxed that is
 * not marked, or a marked table with no column, isolates nothing while the rest of
 * the demo looks isolated. demo:doctor is what checks the three agree, so asserting
 * it is clean is asserting this project's wiring.
 */
test('the doctor is satisfied with how this project is set up for scoped isolation', function (): void {
    $sandboxFindings = array_filter(
        app(Doctor::class)->run(),
        static fn (Finding $f): bool => $f->toArray()['check'] === 'sandbox',
    );

    expect($sandboxFindings)->toBe([]);
});

/**
 * The safety net for the one mistake the setup can leave behind: every other
 * scoped check is skipped when the driver is not scoped, so marking the model and
 * never setting DEMO_SANDBOX=scoped would otherwise pass silently.
 */
test('the doctor warns if this demo ever ships with the models marked and the driver shared', function (): void {
    config()->set('demo.sandbox.driver', 'shared');

    $levels = array_map(
        static fn (Finding $f): string => $f->toArray()['level'],
        array_filter(app(Doctor::class)->run(), static fn (Finding $f): bool => $f->toArray()['check'] === 'sandbox'),
    );

    expect($levels)->toContain('warning');
});

test('a visitor sees the seeded accounts and their own', function (): void {
    $mine = sandbox();

    $admin = userIn(null, 'Seeded Admin');
    $admin->assignRole('user-manager', 'user');

    userIn($mine, 'Account I Made');
    userIn(sandbox(), 'Somebody Else Account');

    $this->actingAs($admin)
        ->withSession([config('demo.sandbox.key') => $mine])
        ->get(route('users.index'))
        ->assertSuccessful()
        ->assertSee('Seeded Admin')
        ->assertSee('Account I Made')
        ->assertDontSee('Somebody Else Account');
});

/**
 * The same page, one visitor later. A fresh session is what a visitor who cleared
 * their cookies has, and it must not carry the previous one's rows — this is the
 * assertion the whole feature is for.
 */
test('the next visitor sees the baseline and nothing the last one made', function (): void {
    $admin = userIn(null, 'Seeded Admin');
    $admin->assignRole('user-manager', 'user');

    userIn(sandbox(), 'Account The Last Visitor Made');

    $this->actingAs($admin)
        ->withSession([config('demo.sandbox.key') => sandbox()])
        ->get(route('users.index'))
        ->assertSuccessful()
        ->assertSee('Seeded Admin')
        ->assertDontSee('Account The Last Visitor Made');
});

/**
 * A visitor who has only looked around has no sandbox at all, and they are the
 * case that leaked: treating "no sandbox" as "no scope" showed every other
 * visitor's rows to anybody who had not written anything, which is most people.
 */
test('a visitor who has created nothing sees only the baseline', function (): void {
    $admin = userIn(null, 'Seeded Admin');
    $admin->assignRole('user-manager', 'user');

    userIn(sandbox(), 'Account Somebody Made');

    $this->actingAs($admin)
        ->get(route('users.index'))
        ->assertSuccessful()
        ->assertSee('Seeded Admin')
        ->assertDontSee('Account Somebody Made');
});

/**
 * The trait is on the authentication model, so this is worth stating outright:
 * the accounts a visitor signs in with are the seeder's, they carry no sandbox
 * id, and they therefore belong to everybody. If that stopped being true nobody
 * could log in to the demo at all.
 */
test('the published account is visible to every visitor', function (): void {
    $admin = userIn(null, 'Seeded Admin');
    $admin->assignRole('user-manager', 'user');

    $this->withSession([config('demo.sandbox.key') => sandbox()]);

    expect(User::whereEmail($admin->email)->exists())->toBeTrue();
});

test('the whole thing is inert on an ordinary installation of this kit', function (): void {
    config()->set('demo.enabled', false);

    User::clearBootedModels();

    userIn(sandbox(), 'Account From A Demo');

    /* No scope at all: the trait adds none when this is not a scoped demo. */
    expect(User::whereName('Account From A Demo')->exists())->toBeTrue();
});
