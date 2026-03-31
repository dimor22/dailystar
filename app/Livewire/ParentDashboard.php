<?php

namespace App\Livewire;

use App\Models\ActivityLog;
use App\Models\Kid;
use App\Models\KidTask;
use App\Models\TaskCompletion;
use App\Models\User;
use App\Services\GamificationService;
use DateTimeZone;
use Illuminate\Support\Carbon;
use Livewire\Component;

class ParentDashboard extends Component
{
    public array $kids = [];

    public array $activityLogs = [];

    public string $activitySearch = '';

    public int $activityDayPage = 1;

    public int $activityDaysPerPage = 1;

    public int $activityTotalDayPages = 1;

    public int $activityTotalDays = 0;

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

        $previousProgressByKid = collect($this->kids)
            ->mapWithKeys(fn (array $kid) => [
                (int) $kid['id'] => [
                    'completed' => (int) ($kid['completed'] ?? 0),
                ],
            ])
            ->all();

        $parent = User::query()
            ->where('role', 'parent')
            ->whereKey($this->parentId)
            ->first();

        if (! $parent) {
            $this->kids = [];
            $this->activityLogs = [];
            $this->parentEmail = '';
            $this->parentTimezone = '';
            $this->activityTotalDayPages = 1;
            $this->activityTotalDays = 0;

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
            ->map(function (Kid $kid) use ($today, $todayWeekday, $gamificationService, $previousProgressByKid) {
                $visibleTasks = KidTask::query()
                    ->with('task:id,title')
                    ->where('kid_id', $kid->id)
                    ->where('active', true)
                    ->where(function ($query) use ($todayWeekday) {
                        $query
                            ->whereNull('days_of_week')
                            ->orWhereJsonLength('days_of_week', 0)
                            ->orWhereJsonContains('days_of_week', $todayWeekday);
                    })
                    ->orderBy('order')
                    ->get()
                    ->filter(fn (KidTask $kidTask) => $kidTask->task !== null)
                    ->values();

                $visibleTaskIds = $visibleTasks
                    ->pluck('task_id')
                    ->all();

                $completedTaskCompletions = TaskCompletion::query()
                    ->with('task:id,title')
                    ->where('kid_id', $kid->id)
                    ->whereDate('completed_date', $today)
                    ->whereIn('task_id', $visibleTaskIds)
                    ->orderBy('completed_at')
                    ->orderBy('created_at')
                    ->get();

                $completedTaskIds = $completedTaskCompletions
                    ->pluck('task_id')
                    ->all();

                $completedTaskIdLookup = array_fill_keys($completedTaskIds, true);

                $completedTaskNames = $completedTaskCompletions
                    ->map(fn (TaskCompletion $taskCompletion) => (string) $taskCompletion->task?->title)
                    ->filter(fn (string $title) => $title !== '')
                    ->values()
                    ->all();

                $pendingTaskNames = $visibleTasks
                    ->filter(fn (KidTask $kidTask) => ! isset($completedTaskIdLookup[$kidTask->task_id]))
                    ->map(fn (KidTask $kidTask) => (string) $kidTask->task?->title)
                    ->filter(fn (string $title) => $title !== '')
                    ->values()
                    ->all();

                $totalTasks = count($visibleTaskIds);
                $completedTasks = count($completedTaskNames);
                $previousCompletedTasks = (int) ($previousProgressByKid[$kid->id]['completed'] ?? 0);
                $justUpdated = ! empty($previousProgressByKid) && $completedTasks > $previousCompletedTasks;

                return [
                    'id' => $kid->id,
                    'name' => $kid->name,
                    'avatar' => $kid->avatar,
                    'avatar_display_mode' => (string) ($kid->avatar_display_mode ?: 'emoji'),
                    'avatar_image_path' => $kid->avatar_image_path,
                    'color' => $kid->color,
                    'points' => $kid->points,
                    'stars' => $gamificationService->starsFromPoints((int) $kid->points),
                    'completed' => $completedTasks,
                    'total' => $totalTasks,
                    'just_updated' => $justUpdated,
                    'completed_task_names' => $completedTaskNames,
                    'pending_task_names' => $pendingTaskNames,
                    'streak' => (int) ($kid->streak->current_streak ?? 0),
                ];
            })
            ->all();

        $timezone = $this->parentTimezone !== '' ? $this->parentTimezone : config('app.timezone');
        $search = strtolower(trim($this->activitySearch));

        $logs = ActivityLog::query()
            ->with(['kid', 'task'])
            ->whereHas('kid', fn ($query) => $query->where('parent_id', $parent->id))
            ->orderByDesc('completed_at')
            ->orderByDesc('created_at')
            ->limit(400)
            ->get()
            ->map(function (ActivityLog $log) use ($timezone) {
                $timestamp = ($log->completed_at ?? $log->created_at)?->copy();
                $kid = $log->kid?->name ?? 'Unknown';
                $task = $log->task?->title ?? '-';
                $action = str_starts_with($log->action, 'completed:') ? 'Completed' : $log->action;

                if ($log->action === 'Daily Bonus') {
                    $task = 'Full Day Completion';
                    $action = 'Daily Bonus (+10 pts)';
                }

                if (! $timestamp) {
                    $timestamp = now();
                }

                $timestamp = $timestamp->setTimezone($timezone);

                return [
                    'kid' => $kid,
                    'task' => $task,
                    'action' => $action,
                    'completed_at' => $timestamp->format('M d, h:i A'),
                    'search_time' => strtolower($timestamp->format('M d, h:i A l F j Y g:i a H:i')),
                    'date_key' => $timestamp->toDateString(),
                    'date_label' => $timestamp->format('l, M j, Y'),
                ];
            })
            ->filter(function (array $log) use ($search) {
                if ($search === '') {
                    return true;
                }

                return str_contains(strtolower($log['kid']), $search)
                    || str_contains(strtolower($log['task']), $search)
                    || str_contains(strtolower($log['action']), $search)
                    || str_contains($log['search_time'], $search);
            })
            ->values();

        $days = $logs
            ->groupBy('date_key')
            ->sortKeysDesc();

        $this->activityTotalDays = $days->count();
        $this->activityTotalDayPages = max(1, (int) ceil($this->activityTotalDays / $this->activityDaysPerPage));
        $this->activityDayPage = min(max(1, $this->activityDayPage), $this->activityTotalDayPages);

        $dayStart = ($this->activityDayPage - 1) * $this->activityDaysPerPage;
        $visibleDays = $days->slice($dayStart, $this->activityDaysPerPage);

        $this->activityLogs = $visibleDays
            ->map(function ($dayLogs, string $dateKey) {
                $firstLog = $dayLogs->first();

                return [
                    'date_key' => $dateKey,
                    'date_label' => $firstLog['date_label'] ?? Carbon::parse($dateKey)->format('l, M j, Y'),
                    'logs' => $dayLogs
                        ->map(fn (array $log) => [
                            'kid' => $log['kid'],
                            'task' => $log['task'],
                            'action' => $log['action'],
                            'completed_at' => $log['completed_at'],
                        ])
                        ->values()
                        ->all(),
                ];
            })
            ->values()
            ->all();
    }

    public function updatedActivitySearch(): void
    {
        $this->activityDayPage = 1;
    }

    public function previousActivityDayPage(): void
    {
        $this->activityDayPage = max(1, $this->activityDayPage - 1);
    }

    public function nextActivityDayPage(): void
    {
        $this->activityDayPage = min($this->activityTotalDayPages, $this->activityDayPage + 1);
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
