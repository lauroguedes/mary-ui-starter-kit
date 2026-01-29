<?php

declare(strict_types=1);

use App\Enums\SocialiteProviders;
use App\Http\Controllers\Auth\SocialAuthController;
use App\Http\Controllers\Auth\VerifyEmailController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::livewire('login', 'pages::auth.login')
        ->name('login');

    Route::livewire('register', 'pages::auth.register')
        ->name('register');

    Route::livewire('forgot-password', 'pages::auth.forgot-password')
        ->name('password.request');

    Route::livewire('reset-password/{token}', 'pages::auth.reset-password')
        ->name('password.reset');

    // Socialite OAuth Routes
    Route::get('/oauth/{provider}/redirect', [SocialAuthController::class, 'redirect'])
        ->whereIn('provider', SocialiteProviders::cases())
        ->name('oauth.redirect');

    Route::get('/oauth/{provider}/callback', [SocialAuthController::class, 'callback'])
        ->whereIn('provider', SocialiteProviders::cases());
});

Route::middleware('auth')->group(function () {
    Route::livewire('verify-email', 'pages::auth.verify-email')
        ->can('user.login')
        ->name('verification.notice');

    Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->can('user.login')
        ->name('verification.verify');

    Route::livewire('confirm-password', 'pages::auth.confirm-password')
        ->can('user.login')
        ->name('password.confirm');
});

Route::post('logout', App\Livewire\Actions\Logout::class)
    ->name('logout');
