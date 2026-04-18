<?php

namespace App\Livewire;

use App\Enums\Plan;
use App\Models\User;
use App\Services\PlanGate;
use Livewire\Component;

/**
 * Dismissable banner shown inline when the user hits a free-plan limit.
 * Usage: <livewire:upgrade-banner :message="'You have reached the 2-kid limit on the Free plan.'" />
 */
class UpgradeBanner extends Component
{
    public string $message = '';

    public bool $visible = false;

    protected $listeners = ['show-upgrade-banner' => 'showBanner'];

    public function mount(string $message = ''): void
    {
        $this->message = $message;

        $userId = (int) session('parent_user_id');

        if ($userId > 0) {
            $user = User::find($userId);
            // Only pre-show if the gate already says the user is at limit
            // (parent components can also call showBanner() via dispatch)
            $this->visible = $user !== null
                && app(PlanGate::class)->planFor($user) === Plan::Free
                && $message !== '';
        }
    }

    /** Called by other Livewire components via $this->dispatch('show-upgrade-banner', message: '...'). */
    public function showBanner(string $message): void
    {
        $this->message  = $message;
        $this->visible  = true;
    }

    public function dismiss(): void
    {
        $this->visible = false;
    }

    public function render(): \Illuminate\View\View
    {
        return view('livewire.upgrade-banner');
    }
}
