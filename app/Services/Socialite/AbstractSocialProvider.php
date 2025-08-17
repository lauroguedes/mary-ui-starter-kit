<?php

declare(strict_types=1);

namespace App\Services\Socialite;

use App\Exceptions\SocialAuthException;
use App\Models\User;
use Exception;
use GuzzleHttp\Exception\ClientException;
use Laravel\Socialite\Contracts\User as ProviderUser;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\InvalidStateException;
use Throwable;

abstract class AbstractSocialProvider
{
    protected string $provider;

    abstract public function handleUser(ProviderUser $socialUser): void;

    /**
     * @throws SocialAuthException
     */
    final public function redirect(): \Symfony\Component\HttpFoundation\RedirectResponse|\Illuminate\Http\RedirectResponse
    {
        try {
            return Socialite::driver($this->provider)->redirect();
        } catch (Exception $e) {
            $this->errorHandler(__('An error occurred while connecting to the social provider.'), $e);
        }
    }

    /**
     * @throws SocialAuthException
     */
    final public function callback(): void
    {
        try {
            $socialUser = Socialite::driver($this->provider)->user();
            $this->handleUser($socialUser);
        } catch (InvalidStateException $e) {
            $this->errorHandler(__('The social authentication state is invalid or expired. Please try again.'), $e);
        } catch (ClientException $e) {
            if ($e->getCode() === 401) {
                $this->errorHandler(__('You have denied the authorization request. Please try again if you want to continue.'), $e);
            }

            $this->errorHandler(__('An error occurred while connecting to the social provider.'), $e);
        } catch (Exception $e) {
            $this->errorHandler(__('An error occurred while connecting to the social provider.'), $e);
        }
    }

    protected function login(User $user): void
    {
        auth()->login($user);
    }

    /**
     * @throws SocialAuthException
     */
    private function errorHandler(
        string $message,
        ?Throwable $previous
    ): void {
        throw new SocialAuthException(
            provider: $this->provider,
            message: $message,
            code: $previous->getCode() ?: 500,
            previous: $previous
        );
    }
}
