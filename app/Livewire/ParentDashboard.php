<?php

namespace App\Livewire;

use App\Models\ActivityLog;
use App\Models\Kid;
use App\Models\TaskCompletion;
use App\Models\User;
use App\Services\GamificationService;
use Livewire\Component;

class ParentDashboard extends Component
{
    public array $kids = [];

    public array $activityLogs = [];

    public function mount(): void
    {
        $this->loadDashboard();
    }

    public function loadDashboard(): void
    {
        $parent = User::query()->where('role', 'parent')->first();

        if (! $parent) {
            $this->kids = [];
            $this->activityLogs = [];

            return;
        }

        $today = now()->toDateString();
        $gamificationService = app(GamificationService::class);

        $this->kids = Kid::query()
            ->with(['tasks', 'streak'])
            ->where('parent_id', $parent->id)
            ->orderBy('name')
            ->get()
            ->map(function (Kid $kid) use ($today, $gamificationService) {
                $totalTasks = $kid->tasks->count();
                $completedTasks = TaskCompletion::query()
                    ->where('kid_id', $kid->id)
                    ->where('completed_date', $today)
                    ->count();

                return [
                    'id' => $kid->id,
                    'name' => $kid->name,
                    'avatar' => $kid->avatar,
                    'color' => $kid->color,
                    'points' => $kid->points,
                    'stars' => $gamificationService->starsFromPoints((int) $kid->points),
                    'completed' => $completedTasks,
                    'total' => $totalTasks,
                    'streak' => (int) ($kid->streak->current_streak ?? 0),
                ];
            })
            ->all();

        $this->activityLogs = ActivityLog::query()
            ->with(['kid', 'task'])
            ->latest('created_at')
            ->limit(20)
            ->get()
            ->map(fn (ActivityLog $log) => [
                'kid' => $log->kid?->name ?? 'Unknown',
                'task' => $log->task?->title ?? '-',
                'action' => $log->action,
                'created_at' => optional($log->created_at)->format('M d, H:i') ?? '',
            ])
            ->all();
    }

    public function render()
    {
        return view('livewire.parent-dashboard');
    }
}
