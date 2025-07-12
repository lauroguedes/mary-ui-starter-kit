<?php

declare(strict_types=1);

namespace App\Services\Socialite;

use Laravel\Socialite\Contracts\User as ProviderUser;
use Laravel\Socialite\Facades\Socialite;

abstract class AbstractSocialProvider
{
    protected string $provider;

    abstract protected function handleUser(ProviderUser $socialUser): void;

    final public function redirect(): \Symfony\Component\HttpFoundation\RedirectResponse|\Illuminate\Http\RedirectResponse
    {
        return Socialite::driver($this->provider)->redirect();
    }

    final public function callback(): void
    {
        $socialUser = Socialite::driver($this->provider)->user();

        ds($socialUser);

        $this->handleUser($socialUser);
    }
}
