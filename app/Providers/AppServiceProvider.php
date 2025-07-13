<?php

declare(strict_types=1);

namespace App\Providers;

use App\Enums\SocialiteProviders;
use App\Services\Socialite\GoogleProvider;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use InvalidArgumentException;

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
        Route::bind('provider', function (string $value) {
            return SocialiteProviders::from($value)->make();
        });
    }
}
