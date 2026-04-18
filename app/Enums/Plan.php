<?php

namespace App\Enums;

enum Plan: string
{
    case Free = 'free';
    case Pro  = 'pro';

    /** Maximum kids on the free plan. Pro has no limit. */
    public const FREE_KID_LIMIT = 2;

    /** Maximum active tasks assigned to a single kid on the free plan. Pro has no limit. */
    public const FREE_TASK_LIMIT = 5;

    /** Features available on each plan. */
    public const PRO_FEATURES = [
        'unlimited_kids',
        'unlimited_tasks',
        'streak_bonuses',
        'points_store',
        'star_rewards',
        'celebration_animations',
        'parent_insights',
    ];

    public function label(): string
    {
        return match ($this) {
            self::Free => 'Free',
            self::Pro  => 'Pro',
        };
    }

    public function isPro(): bool
    {
        return $this === self::Pro;
    }
}
