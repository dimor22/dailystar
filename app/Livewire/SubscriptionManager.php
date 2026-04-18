<?php

namespace App\Livewire;

use App\Enums\Plan;
use App\Models\User;
use App\Services\PlanGate;
use Livewire\Component;

class SubscriptionManager extends Component
{
    public int $userId = 0;

    public function mount(): void
    {
        $this->userId = (int) session('parent_user_id');
    }

    // ─── Computed helpers (not cached — called inside render) ─────────────────

    private function user(): ?User
    {
        return $this->userId > 0 ? User::find($this->userId) : null;
    }

    public function render(): \Illuminate\View\View
    {
        $user         = $this->user();
        $plan         = $user ? app(PlanGate::class)->planFor($user) : Plan::Free;
        $subscription = $user?->subscription('pro');

        $isActive       = $subscription?->active() ?? false;
        $onTrial        = $subscription?->onTrial() ?? false;
        $onGracePeriod  = $subscription?->onGracePeriod() ?? false;
        $trialEndsAt    = $subscription?->trial_ends_at;
        $endsAt         = $subscription?->ends_at;

        return view('livewire.subscription-manager', compact(
            'plan',
            'isActive',
            'onTrial',
            'onGracePeriod',
            'trialEndsAt',
            'endsAt',
        ));
    }
}
