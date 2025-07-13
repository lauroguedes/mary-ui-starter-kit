<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

final class SocialAuthException extends Exception
{
    private readonly string $provider;

    public function __construct(
        string $provider,
        string $message = '',
        int $code = 0,
        ?Exception $previous = null
    ) {
        parent::__construct(
            $message,
            $code,
            $previous
        );

        $this->provider = $provider;
    }

    public function report(): bool
    {
        Log::warning(
            'Social authentication failed.',
            ['message' => $this->getMessage(), 'provider' => $this->provider]
        );

        return false;
    }

    public function render(Request $request): \Illuminate\Http\RedirectResponse
    {
        return redirect()
            ->route('login')
            ->with('error', $this->getMessage());
    }
}
