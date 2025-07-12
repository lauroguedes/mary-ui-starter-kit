<?php

declare(strict_types=1);

namespace App\Services\Socialite;

use App\Models\User;
use Laravel\Socialite\Contracts\User as ProviderUser;

final class GoogleProvider extends AbstractSocialProvider
{
    protected function handleUser(ProviderUser $socialUser): User
    {
        // TODO: Implement handleUser() method.
    }
}
