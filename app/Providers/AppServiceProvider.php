<?php

declare(strict_types=1);

namespace App\Providers;

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
        // $this->app->bind('google', fn () => new GoogleProvider());
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Route::bind('provider', function (string $value) {
            return match ($value) {
                'google' => new GoogleProvider(),
                default => throw new InvalidArgumentException('Invalid provider'),
            };
        });
    }
}
