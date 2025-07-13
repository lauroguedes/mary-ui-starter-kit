<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Socialite;

use App\Models\SocialAccount;
use App\Models\User;
use App\Services\Socialite\GoogleProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Contracts\User as ProviderUser;
use Mockery;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    Auth::shouldReceive('login')->andReturnNull();
});

afterEach(function () {
    Mockery::close();
});

function createSocialUserMock(array $attributes = []): ProviderUser
{
    $defaults = [
        'id' => fake()->uuid(),
        'email' => fake()->email(),
        'name' => fake()->name(),
        'avatar' => fake()->imageUrl(),
    ];

    $data = array_merge($defaults, $attributes);

    $socialUser = Mockery::mock(ProviderUser::class);
    $socialUser->shouldReceive('getId')->andReturn($data['id']);
    $socialUser->shouldReceive('getEmail')->andReturn($data['email']);
    $socialUser->shouldReceive('getName')->andReturn($data['name']);
    $socialUser->shouldReceive('getAvatar')->andReturn($data['avatar']);

    return $socialUser;
}

function createSocialAccount(User $user, string $providerId): SocialAccount
{
    return SocialAccount::factory()->create([
        'provider_name' => 'google',
        'provider_id' => $providerId,
        'user_id' => $user->id,
    ]);
}

function assertSessionData(string $avatar): void
{
    expect(session('auth_provider'))->toBe([
        'name' => 'google',
        'avatar' => $avatar,
    ]);
}

describe('GoogleProvider', function () {
    it('logs in existing user with linked social account', function () {
        $provider = new GoogleProvider();
        $providerId = fake()->uuid();
        $avatarUrl = fake()->imageUrl();

        $socialUser = createSocialUserMock([
            'id' => $providerId,
            'avatar' => $avatarUrl,
        ]);

        $user = User::factory()->create();
        createSocialAccount($user, $providerId);

        $provider->handleUser($socialUser);

        assertSessionData($avatarUrl);
    });

    it('creates new user and links social account', function () {
        $provider = new GoogleProvider();
        $email = fake()->email();
        $name = fake()->name();
        $avatarUrl = fake()->imageUrl();

        $socialUser = createSocialUserMock([
            'email' => $email,
            'name' => $name,
            'avatar' => $avatarUrl,
        ]);

        $provider->handleUser($socialUser);

        $this->assertDatabaseHas('users', [
            'email' => $email,
            'name' => $name,
        ]);

        $user = User::where('email', $email)->first();

        expect($user)->not->toBeNull()
            ->and($user->hasVerifiedEmail())->toBeTrue();

        $this->assertDatabaseHas('social_accounts', [
            'provider_name' => 'google',
            'provider_id' => $socialUser->getId(),
            'avatar' => $avatarUrl,
            'user_id' => $user->id,
        ]);

        assertSessionData($avatarUrl);
    });

    it('prevents duplicate user creation for same email', function () {
        $provider = new GoogleProvider();
        $email = fake()->email();
        $providerId = fake()->uuid();
        $newAvatar = fake()->imageUrl();

        $socialUser = createSocialUserMock([
            'id' => $providerId,
            'email' => $email,
            'avatar' => $newAvatar,
        ]);

        $user = User::factory()->create(['email' => $email]);
        $user->socialAccounts()->create([
            'provider_name' => 'google',
            'provider_id' => $providerId,
            'avatar' => fake()->imageUrl(),
        ]);

        $provider->handleUser($socialUser);

        expect(User::where('email', $email)->count())->toBe(1)
            ->and(SocialAccount::where('provider_id', $providerId)->count())->toBe(1);

        assertSessionData($newAvatar);
    });

    it('updates existing user with new social account data', function () {
        $provider = new GoogleProvider();
        $email = fake()->email();
        $originalName = fake()->name();
        $newName = fake()->name();
        $avatarUrl = fake()->imageUrl();

        $socialUser = createSocialUserMock([
            'email' => $email,
            'name' => $newName,
            'avatar' => $avatarUrl,
        ]);

        $user = User::factory()->create([
            'email' => $email,
            'name' => $originalName,
        ]);

        $provider->handleUser($socialUser);

        $user->refresh();
        $account = SocialAccount::where('provider_name', 'google')
            ->where('provider_id', $socialUser->getId())
            ->first();

        expect($user->name)->toBe($newName)
            ->and($account)->not->toBeNull()
            ->and($account->avatar)->toBe($avatarUrl);

        assertSessionData($avatarUrl);
    });

    it('marks email as verified for new users', function () {
        $provider = new GoogleProvider();
        $email = fake()->email();

        $socialUser = createSocialUserMock(['email' => $email]);

        $provider->handleUser($socialUser);

        $user = User::where('email', $email)->first();

        expect($user->hasVerifiedEmail())->toBeTrue();
    });

    it('preserves email verification status for existing verified users', function () {
        $provider = new GoogleProvider();
        $email = fake()->email();

        $socialUser = createSocialUserMock(['email' => $email]);

        $user = User::factory()->create([
            'email' => $email,
            'email_verified_at' => now(),
        ]);

        $provider->handleUser($socialUser);

        $user->refresh();

        expect($user->hasVerifiedEmail())->toBeTrue();
    });

    it('sets correct session data with provider information', function () {
        $provider = new GoogleProvider();
        $avatarUrl = fake()->imageUrl();

        $socialUser = createSocialUserMock(['avatar' => $avatarUrl]);

        $provider->handleUser($socialUser);

        assertSessionData($avatarUrl);
    });
});
