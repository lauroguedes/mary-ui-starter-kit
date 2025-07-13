<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;
use Illuminate\Http\RedirectResponse;
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
