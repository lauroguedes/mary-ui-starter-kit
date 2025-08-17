<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Exceptions\SocialAuthException;
use App\Http\Controllers\Controller;
use App\Services\Socialite\AbstractSocialProvider;

final class SocialAuthController extends Controller
{
    /**
     * @throws SocialAuthException
     */
    public function redirect(AbstractSocialProvider $provider): \Symfony\Component\HttpFoundation\RedirectResponse|\Illuminate\Http\RedirectResponse
    {
        return $provider->redirect();
    }

    /**
     * @throws SocialAuthException
     */
    public function callback(AbstractSocialProvider $provider): \Illuminate\Http\RedirectResponse
    {
        $provider->callback();

        if (auth()->user()->cannot('dashboard.view')) {
            return redirect()->route('settings.profile');
        }

        return redirect()->route('dashboard');
    }
}
