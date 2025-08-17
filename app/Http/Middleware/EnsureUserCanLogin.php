<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Enums\UserStatus;
use App\Livewire\Actions\Logout;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

final readonly class EnsureUserCanLogin
{
    public function __construct(
        private Logout $logout
    ) {}

    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     *
     * @throws ValidationException
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (
            $request->user()->status === UserStatus::SUSPENDED
            || $request->user()->cannot('user.login')
        ) {
            ($this->logout)();

            throw ValidationException::withMessages([
                'email' => __('User cannot log in.'),
            ]);
        }

        return $next($request);
    }
}
