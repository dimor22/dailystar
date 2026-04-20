<?php

namespace App\Services;

use App\Enums\Plan;
use App\Enums\Role;
use App\Models\Kid;
use App\Models\KidTask;
use App\Models\User;

class PlanGate
{
    /**
     * Resolve the active plan for a user.
     * Returns Pro if the user has an active (or on-trial) Cashier subscription named 'pro'.
     */
    public function planFor(User $user): Plan
    {
        // Early adopters and admins get Pro access without a subscription.
        $role = $user->role instanceof Role ? $user->role : Role::tryFrom((string) $user->role);
        if ($role && $role->hasFreeProAccess()) {
            return Plan::Pro;
        }

        if ($user->subscribed('pro')) {
            return Plan::Pro;
        }

        return Plan::Free;
    }

    /**
     * Whether the user can add another kid.
     */
    public function canCreateKid(User $user): bool
    {
        if ($this->planFor($user)->isPro()) {
            return true;
        }

        return $user->kids()->count() < Plan::FREE_KID_LIMIT;
    }

    /**
     * Whether a specific kid can have more active tasks assigned.
     */
    public function canAssignTask(User $user, Kid $kid): bool
    {
        if ($this->planFor($user)->isPro()) {
            return true;
        }

        $activeCount = KidTask::query()
            ->where('kid_id', $kid->id)
            ->where('active', true)
            ->count();

        return $activeCount < Plan::FREE_TASK_LIMIT;
    }

    /**
     * Whether the user has access to a specific named feature.
     */
    public function hasFeature(User $user, string $feature): bool
    {
        if ($this->planFor($user)->isPro()) {
            return true;
        }

        // Free plan always has these basic features.
        return in_array($feature, ['basic_tasks', 'star_rewards', 'daily_progress'], true);
    }

    /**
     * How many kids the user is currently over the free limit by (0 if fine).
     */
    public function kidLimitOverageFor(User $user): int
    {
        if ($this->planFor($user)->isPro()) {
            return 0;
        }

        return max(0, $user->kids()->count() - Plan::FREE_KID_LIMIT);
    }
}
