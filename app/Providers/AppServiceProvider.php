<?php

declare(strict_types=1);

namespace App\Providers;

use App\Enums\SocialiteProviders;
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
    }
}
