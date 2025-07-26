<?php

declare(strict_types=1);

use App\Enums\SocialiteProviders;
use App\Http\Controllers\Auth\SocialAuthController;
use App\Http\Controllers\Auth\VerifyEmailController;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::middleware('guest')->group(function () {
    Volt::route('login', 'auth.login')
        ->name('login');

    Volt::route('register', 'auth.register')
        ->name('register');

    Volt::route('forgot-password', 'auth.forgot-password')
        ->name('password.request');

    Volt::route('reset-password/{token}', 'auth.reset-password')
        ->name('password.reset');

    // Socialite OAuth Routes
    Route::get('/oauth/{provider}/redirect', [SocialAuthController::class, 'redirect'])
        ->whereIn('provider', SocialiteProviders::cases())
        ->name('oauth.redirect');

    Route::get('/oauth/{provider}/callback', [SocialAuthController::class, 'callback'])
        ->whereIn('provider', SocialiteProviders::cases());
});

Route::middleware('auth')->group(function () {
    Volt::route('verify-email', 'auth.verify-email')
        ->can('user.login')
        ->name('verification.notice');

    Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->can('user.login')
        ->name('verification.verify');

    Volt::route('confirm-password', 'auth.confirm-password')
        ->can('user.login')
        ->name('password.confirm');
});

Route::post('logout', App\Livewire\Actions\Logout::class)
    ->name('logout');
