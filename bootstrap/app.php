<?php

declare(strict_types=1);

use App\Exceptions\SocialAuthException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (Throwable $e, Request $request) {
            if ($request->is('oauth/*')) {
                return redirect()->route('login')
                    ->with('error', 'An unexpected error occurred. Please try again later.');
            }
        });

        $exceptions->report(function (Throwable $e, Request $request) {
            if ($request->is('oauth/*')) {
                Log::error('Unexpected error during social auth redirect', [
                    'provider' => $request->route('provider'),
                    'error' => $e->getMessage(),
                ]);
            }
        })->stop();
    })->create();
