<?php

declare(strict_types=1);

use App\Http\Middleware\EnsureUserCanLogin;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'can-login' => EnsureUserCanLogin::class,
        ]);

        /*
         * Keeps a visitor's corner of the demo alive while they are using it.
         *
         * Safe here permanently: the alias is registered on every installation and
         * the middleware does nothing unless demo.sandbox.driver is 'scoped'. It has
         * to run after the session, because that is where the identifier lives,
         * which is what appending to the web group buys.
         *
         * Without it a sandbox is pruned an hour after it was created rather than an
         * hour after its visitor stopped — a TTL instead of a deadline.
         */
        $middleware->web(append: ['demo.sandbox']);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
