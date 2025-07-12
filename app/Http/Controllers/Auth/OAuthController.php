<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Laravel\Socialite\Facades\Socialite;

final class OAuthController extends Controller
{
    public function redirect(string $driver): \Symfony\Component\HttpFoundation\RedirectResponse|\Illuminate\Http\RedirectResponse
    {
        return Socialite::driver($driver)->redirect();
    }

    public function callback(string $driver): \Illuminate\Http\RedirectResponse
    {
        $user = Socialite::driver($driver)->user();

        ds($user);

        return redirect()->route('dashboard');
    }
}
