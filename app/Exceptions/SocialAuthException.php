<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

final class SocialAuthException extends Exception
{
    public function __construct(
        private readonly string $provider,
        string $message = '',
        int $code = 0,
        ?Exception $previous = null
    ) {
        parent::__construct(
            $message,
            $code,
            $previous
        );
    }

    public function report(): bool
    {
        Log::error(
            'Social authentication failed.',
            [
                'message' => $this->getMessage(),
                'provider' => $this->provider,
                'code' => $this->getCode(),
                'previous' => $this->getPrevious(),
            ]
        );

        return false;
    }

    public function render(Request $request): RedirectResponse
    {
        return redirect()
            ->route('login')
            ->with('error', $this->getMessage());
    }
}
