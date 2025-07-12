<?php

declare(strict_types=1);

namespace App\Http\Controllers\OAuth;

use App\Http\Controllers\Controller;
use Laravel\Socialite\Facades\Socialite;

final class GoogleController extends Controller
{
    public function redirect(): \Symfony\Component\HttpFoundation\RedirectResponse|\Illuminate\Http\RedirectResponse
    {
        return Socialite::driver('google')->redirect();
    }

    public function callback(): \Illuminate\Http\RedirectResponse
    {
        $user = Socialite::driver('google')->user();

        ds($user);

        return redirect()->route('dashboard');
    }
}
