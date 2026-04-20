<?php

namespace App\Livewire;

use App\Enums\Role;
use App\Models\Kid;
use App\Models\Task;
use App\Models\TaskCompletion;
use App\Models\User;
use Illuminate\Support\Carbon;
use Livewire\Component;

class AdminDashboard extends Component
{
    public string $search = '';

    public string $roleFilter = '';

    // ─── Computed stats ──────────────────────────────────────────────────────

    private function stats(): array
    {
        $totalUsers    = User::query()->whereIn('role', ['parent', 'early_adopter'])->count();
        $earlyAdopters = User::query()->where('role', Role::EarlyAdopter->value)->count();
        $proUsers      = User::query()->whereHas('subscriptions', fn ($q) => $q->where('stripe_status', 'active'))->count();
        $totalKids     = Kid::query()->count();
        $totalTasks    = Task::query()->count();

        $activeThreshold = Carbon::now()->subDays(7);
        $activeUsers = User::query()
            ->whereIn('role', ['parent', 'early_adopter'])
            ->whereHas('kids', function ($q) use ($activeThreshold) {
                $q->whereHas('taskCompletions', fn ($q2) => $q2->where('completed_date', '>=', $activeThreshold->toDateString()));
            })
            ->count();

        $completionsToday = TaskCompletion::query()
            ->whereDate('completed_date', Carbon::today()->toDateString())
            ->count();

        return compact('totalUsers', 'earlyAdopters', 'proUsers', 'totalKids', 'totalTasks', 'activeUsers', 'completionsToday');
    }

    // ─── User list ───────────────────────────────────────────────────────────

    private function userQuery()
    {
        $query = User::query()
            ->withCount('kids')
            ->whereIn('role', ['parent', 'early_adopter', 'admin'])
            ->orderByDesc('created_at');

        if ($this->search !== '') {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('email', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->roleFilter !== '') {
            $query->where('role', $this->roleFilter);
        }

        return $query;
    }

    // ─── Actions ─────────────────────────────────────────────────────────────

    public function setRole(int $userId, string $role): void
    {
        $allowed = [Role::Parent->value, Role::EarlyAdopter->value, Role::Admin->value];

        if (! in_array($role, $allowed, true)) {
            return;
        }

        // Prevent demoting yourself so you don't get locked out.
        if ($userId === (int) session('parent_user_id')) {
            $this->dispatch('toast', message: 'You cannot change your own role.', type: 'warning');
            return;
        }

        User::query()->where('id', $userId)->update(['role' => $role]);
        $this->dispatch('toast', message: 'Role updated.', type: 'success');
    }

    public function render(): \Illuminate\View\View
    {
        return view('livewire.admin-dashboard', [
            'stats' => $this->stats(),
            'users' => $this->userQuery()->paginate(20),
            'roles' => Role::cases(),
        ]);
    }
}
