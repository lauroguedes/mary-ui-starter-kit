<?php

declare(strict_types=1);

namespace App\Services\Socialite;

use App\Models\User;
use Laravel\Socialite\Contracts\User as ProviderUser;
use Laravel\Socialite\Facades\Socialite;

abstract class AbstractSocialProvider
{
    protected string $provider;

    abstract protected function handleUser(ProviderUser $socialUser): User;

    final public function redirect(): \Symfony\Component\HttpFoundation\RedirectResponse|\Illuminate\Http\RedirectResponse
    {
        return Socialite::driver($this->provider)->redirect();
    }

    final public function callback(): User
    {
        $socialUser = Socialite::driver($this->provider)->user();

        return $this->handleUser($socialUser);
    }
}
