<?php

namespace App\Livewire;

use App\Enums\Role;
use App\Models\Kid;
use App\Models\Task;
use App\Models\TaskCompletion;
use App\Models\User;
use Illuminate\Support\Carbon;
use Livewire\Component;
use Livewire\WithPagination;

class AdminDashboard extends Component
{
    use WithPagination;

    public string $search = '';

    public string $roleFilter = '';

    // Role to assign before approving, keyed by user id
    public array $pendingRoles = [];

    // ─── Computed stats ──────────────────────────────────────────────────────

    private function stats(): array
    {
        $totalUsers    = User::query()->whereIn('role', ['parent', 'early_adopter'])->where('status', 'active')->count();
        $earlyAdopters = User::query()->where('role', Role::EarlyAdopter->value)->where('status', 'active')->count();
        $proUsers      = User::query()->whereHas('subscriptions', fn ($q) => $q->where('stripe_status', 'active'))->count();
        $totalKids     = Kid::query()->count();
        $totalTasks    = Task::query()->count();
        $pendingCount  = User::query()->where('status', 'pending')->count();

        $activeThreshold = Carbon::now()->subDays(7);
        $activeUsers = User::query()
            ->whereIn('role', ['parent', 'early_adopter'])
            ->where('status', 'active')
            ->whereHas('kids', function ($q) use ($activeThreshold) {
                $q->whereHas('taskCompletions', fn ($q2) => $q2->where('completed_date', '>=', $activeThreshold->toDateString()));
            })
            ->count();

        $completionsToday = TaskCompletion::query()
            ->whereDate('completed_date', Carbon::today()->toDateString())
            ->count();

        return compact('totalUsers', 'earlyAdopters', 'proUsers', 'totalKids', 'totalTasks', 'activeUsers', 'completionsToday', 'pendingCount');
    }

    // ─── Pending users ───────────────────────────────────────────────────────

    private function pendingUsers()
    {
        return User::query()
            ->where('status', 'pending')
            ->orderBy('created_at')
            ->get();
    }

    // ─── Active user list ────────────────────────────────────────────────────

    private function userQuery()
    {
        $query = User::query()
            ->withCount('kids')
            ->where('status', 'active')
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

    public function approveUser(int $userId): void
    {
        $user = User::find($userId);

        if (! $user || $user->status !== 'pending') {
            return;
        }

        $role = $this->pendingRoles[$userId] ?? Role::Parent->value;
        $allowed = [Role::Parent->value, Role::EarlyAdopter->value, Role::Admin->value];

        if (! in_array($role, $allowed, true)) {
            $role = Role::Parent->value;
        }

        $user->update(['status' => 'active', 'role' => $role]);
        unset($this->pendingRoles[$userId]);

        $this->dispatch('toast', message: "{$user->name} has been approved.", type: 'success');
    }

    public function rejectUser(int $userId): void
    {
        $user = User::find($userId);

        if (! $user || $user->status !== 'pending') {
            return;
        }

        $name = $user->name;
        $user->delete();
        unset($this->pendingRoles[$userId]);

        $this->dispatch('toast', message: "{$name}'s registration was rejected and removed.", type: 'warning');
    }

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
            'stats'        => $this->stats(),
            'pendingUsers' => $this->pendingUsers(),
            'users'        => $this->userQuery()->paginate(20),
            'roles'        => Role::cases(),
        ]);
    }
}
