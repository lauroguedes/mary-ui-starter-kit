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

    protected function handleUser(ProviderUser $socialUser): void
    {
        session(['auth_provider' => [
            'name' => $this->provider,
            'avatar' => $socialUser->avatar,
        ]]);

        $account = SocialAccount::whereProviderName($this->provider)
            ->whereProviderId($socialUser->id)
            ->first();

        if ($account) {
            Auth::login($account->user);

            return;
        }

        $user = User::updateOrCreate([
            'email' => $socialUser->email,
        ], [
            'name' => $socialUser->name,
            'email' => $socialUser->email,
        ]);

        if (! $user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
        }

        $account = $user->socialAccounts()->create([
            'provider_name' => $this->provider,
            'provider_id' => $socialUser->id,
            'avatar' => $socialUser->avatar,
        ]);

        Auth::login($user);
    }
}
