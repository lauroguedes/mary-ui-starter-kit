<?php

declare(strict_types=1);

namespace App\Enums;

enum UserStatus: int
{
    case ACTIVE = 1;
    case INACTIVE = 2;
    case SUSPENDED = 3;

    public static function fromLabel(string $label): ?self
    {
        return match (mb_strtolower($label)) {
            'active' => self::ACTIVE,
            'inactive' => self::INACTIVE,
            'suspended' => self::SUSPENDED,
            default => null,
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::ACTIVE => 'Active',
            self::INACTIVE => 'Inactive',
            self::SUSPENDED => 'Suspended',
        };
    }
}
