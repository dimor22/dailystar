<?php

namespace App\Enums;

enum Role: string
{
    case Parent       = 'parent';
    case EarlyAdopter = 'early_adopter';
    case Admin        = 'admin';

    public function label(): string
    {
        return match ($this) {
            self::Parent       => 'Parent',
            self::EarlyAdopter => 'Early Adopter',
            self::Admin        => 'Admin',
        };
    }

    /**
     * Roles that get full Pro-level feature access without a paid subscription.
     */
    public function hasFreeProAccess(): bool
    {
        return match ($this) {
            self::EarlyAdopter, self::Admin => true,
            default                         => false,
        };
    }
}
