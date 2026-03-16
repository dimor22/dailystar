<?php

namespace App\Livewire;

use App\Models\ActivityLog;
use App\Models\Kid;
use App\Models\KidTask;
use App\Models\TaskCompletion;
use App\Models\User;
use App\Services\GamificationService;
use DateTimeZone;
use Livewire\Component;

class ParentDashboard extends Component
{
    public array $kids = [];

    public array $activityLogs = [];

    public int $parentId = 0;

    public string $parentEmail = '';

    public string $parentTimezone = '';

    public string $timezone = '';

    public string $dashboardDateTime = '';

    public function mount(): void
    {
        $this->parentId = (int) session('parent_user_id');
        $this->loadDashboard();
    }

    public function loadDashboard(): void
    {
        $this->dashboardDateTime = now()->format('l, F j • h:i A');

        $parent = User::query()
            ->where('role', 'parent')
            ->whereKey($this->parentId)
            ->first();

        if (! $parent) {
            $this->kids = [];
            $this->activityLogs = [];
            $this->parentEmail = '';
            $this->parentTimezone = '';

            return;
        }

        $this->parentEmail = (string) $parent->email;
        $this->parentTimezone = (string) ($parent->timezone ?: config('app.timezone'));

        if ($this->timezone === '') {
            $this->timezone = $this->parentTimezone;
        }

        $today = now()->toDateString();
        $todayWeekday = strtolower(now()->format('l'));
        $gamificationService = app(GamificationService::class);

        $this->kids = Kid::query()
            ->with(['tasks', 'streak'])
            ->where('parent_id', $parent->id)
            ->orderBy('name')
            ->get()
            ->map(function (Kid $kid) use ($today, $todayWeekday, $gamificationService) {
                $visibleTaskIds = KidTask::query()
                    ->where('kid_id', $kid->id)
                    ->where('active', true)
                    ->where(function ($query) use ($todayWeekday) {
                        $query
                            ->whereNull('days_of_week')
                            ->orWhereJsonLength('days_of_week', 0)
                            ->orWhereJsonContains('days_of_week', $todayWeekday);
                    })
                    ->pluck('task_id')
                    ->all();

                $totalTasks = count($visibleTaskIds);
                $completedTasks = TaskCompletion::query()
                    ->where('kid_id', $kid->id)
                    ->whereDate('completed_date', $today)
                    ->whereIn('task_id', $visibleTaskIds)
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
            ->whereHas('kid', fn ($query) => $query->where('parent_id', $parent->id))
            ->orderByDesc('completed_at')
            ->orderByDesc('created_at')
            ->limit(20)
            ->get()
            ->map(fn (ActivityLog $log) => [
                'kid' => $log->kid?->name ?? 'Unknown',
                'task' => $log->task?->title ?? '-',
                'action' => str_starts_with($log->action, 'completed:')
                    ? 'Completed'
                    : $log->action,
                'completed_at' => optional($log->completed_at ?? $log->created_at)->format('M d, h:i A') ?? '',
            ])
            ->all();
    }

    public function updateTimezone(): void
    {
        $validated = $this->validate([
            'timezone' => ['required', 'timezone'],
        ]);

        $parent = User::query()
            ->where('role', 'parent')
            ->whereKey($this->parentId)
            ->first();

        abort_unless($parent, 403);

        $parent->timezone = $validated['timezone'];
        $parent->save();

        session(['parent_timezone' => $parent->timezone]);
        config(['app.timezone' => $parent->timezone]);
        date_default_timezone_set($parent->timezone);

        $this->parentTimezone = (string) $parent->timezone;
        $this->timezone = (string) $parent->timezone;

        $this->dispatch('toast', message: 'Timezone updated successfully.', type: 'success');
    }

    public function render()
    {
        return view('livewire.parent-dashboard', [
            'timezones' => DateTimeZone::listIdentifiers(),
        ]);
    }
}
