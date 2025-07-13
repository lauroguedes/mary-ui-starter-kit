<?php

declare(strict_types=1);

namespace App\Enums;

use App\Services\Socialite\AbstractSocialProvider;
use App\Services\Socialite\GoogleProvider;
use InvalidArgumentException;

enum SocialiteProviders: string
{
    case GOOGLE = 'google';

    public function make(): AbstractSocialProvider
    {
        return match ($this) {
            self::GOOGLE => new GoogleProvider(),
            default => throw new InvalidArgumentException('Invalid social provider'),
        };
    }
}
