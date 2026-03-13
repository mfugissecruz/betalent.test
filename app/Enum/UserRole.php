<?php

declare(strict_types = 1);

namespace App\Enum;

enum UserRole: string
{
    case ADMIN   = 'ADMIN';
    case MANAGER = 'MANAGER';
    case FINANCE = 'FINANCE';
    case USER    = 'USER';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function allows(self ...$roles): string
    {
        return 'role:' . implode(',', array_map(fn ($role) => $role->value, $roles));
    }
}
