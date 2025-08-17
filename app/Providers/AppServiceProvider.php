<?php

declare(strict_types=1);

namespace App\Providers;

use App\Enums\SocialiteProviders;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

final class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Route::bind('provider', fn (string $value) => SocialiteProviders::from($value)->make());

        /*
         * This explicit binding was necessary because
         * the 'Volt::route' does not support implicit route model binding,
         * preventing the model policy from triggering.
         *
         * This is a temporary solution until the issue is resolved.
         * https://github.com/livewire/volt/issues/104
         * */
        Route::model('user', \App\Models\User::class);

        Gate::before(fn ($user, $ability): ?bool => $user->hasRole('super-admin') ? true : null);
    }
}
