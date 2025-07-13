<?php

declare(strict_types=1);

namespace App\Services\Socialite;

use App\Enums\SocialiteProviders;
use App\Models\SocialAccount;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Contracts\User as ProviderUser;

final class GoogleProvider extends AbstractSocialProvider
{
    public function __construct()
    {
        $this->provider = SocialiteProviders::GOOGLE->value;
    }

    public function handleUser(ProviderUser $socialUser): void
    {
        session(['auth_provider' => [
            'name' => $this->provider,
            'avatar' => $socialUser->getAvatar(),
        ]]);

        $account = SocialAccount::whereProviderName($this->provider)
            ->whereProviderId($socialUser->getId())
            ->first();

        if ($account) {
            Auth::login($account->user);

            return;
        }

        $user = User::updateOrCreate([
            'email' => $socialUser->getEmail(),
        ], [
            'name' => $socialUser->getName(),
            'email' => $socialUser->getEmail(),
        ]);

        if (! $user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
        }

        $user->socialAccounts()->create([
            'provider_name' => $this->provider,
            'provider_id' => $socialUser->getId(),
            'avatar' => $socialUser->getAvatar(),
        ]);

        Auth::login($user);
    }
}
