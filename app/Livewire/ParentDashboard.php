<?php

namespace App\Livewire;

use App\Models\ActivityLog;
use App\Models\Kid;
use App\Models\KidTask;
use App\Models\PointsStoreItem;
use App\Models\TaskCompletion;
use App\Models\User;
use DateTimeZone;
use Illuminate\Support\Carbon;
use Livewire\Component;

class ParentDashboard extends Component
{
    public array $kids = [];

    public array $rewardStatuses = [];

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
            $this->rewardStatuses = [];
            $this->activityLogs = [];
            $this->parentEmail = '';
            $this->parentTimezone = '';
            $this->activityTotalDayPages = 1;
            $this->activityTotalDays = 0;

            return;
        }

        $this->parentEmail = (string) $parent->email;
        $this->parentTimezone = (string) ($parent->timezone ?: config('app.timezone'));
        $timezone = $this->resolveDisplayTimezone($this->parentTimezone);
        $this->dashboardDateTime = now('UTC')->setTimezone($timezone)->format('l, F j • h:i A');

        if ($this->timezone === '') {
            $this->timezone = $timezone;
        }

        $today = now()->toDateString();
        $todayWeekday = strtolower(now()->format('l'));
        $kidModels = Kid::query()
            ->with(['tasks', 'streak'])
            ->where('parent_id', $parent->id)
            ->orderBy('name')
            ->get();

        $this->kids = $kidModels
            ->map(function (Kid $kid) use ($today, $todayWeekday, $previousProgressByKid) {
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
                    'stars' => (int) $kid->stars,
                    'completed' => $completedTasks,
                    'total' => $totalTasks,
                    'just_updated' => $justUpdated,
                    'completed_task_names' => $completedTaskNames,
                    'pending_task_names' => $pendingTaskNames,
                    'streak' => (int) ($kid->streak->current_streak ?? 0),
                ];
            })
            ->all();

        $timezone = $this->resolveDisplayTimezone($this->timezone !== '' ? $this->timezone : $this->parentTimezone);
        $search = strtolower(trim($this->activitySearch));

        $rewardLogs = ActivityLog::query()
            ->with('kid')
            ->whereHas('kid', fn ($query) => $query->where('parent_id', $parent->id))
            ->where(function ($query) {
                $query
                    ->where('action', 'like', 'Redeemed Reward:%')
                    ->orWhere('action', 'like', 'Reward Fulfilled: #%');
            })
            ->orderByDesc('completed_at')
            ->orderByDesc('created_at')
            ->limit(600)
            ->get();

        $fulfilledRedemptionIds = $rewardLogs
            ->filter(fn (ActivityLog $log) => str_starts_with((string) $log->action, 'Reward Fulfilled: #'))
            ->map(function (ActivityLog $log) {
                if (preg_match('/^Reward Fulfilled: #(\d+)/', (string) $log->action, $matches) !== 1) {
                    return null;
                }

                return (int) $matches[1];
            })
            ->filter()
            ->values()
            ->all();

        $pendingRewardRedemptions = $rewardLogs
            ->filter(fn (ActivityLog $log) => str_starts_with((string) $log->action, 'Redeemed Reward: '))
            ->reject(fn (ActivityLog $log) => in_array((int) $log->id, $fulfilledRedemptionIds, true))
            ->map(function (ActivityLog $log) use ($timezone) {
                $timestamp = $this->displayTimestampFromLog($log, $timezone);

                return [
                    'id' => (int) $log->id,
                    'kid_id' => (int) $log->kid_id,
                    'kid' => (string) ($log->kid?->name ?? 'Unknown'),
                    'item' => (string) str_replace('Redeemed Reward: ', '', (string) $log->action),
                    'redeemed_at' => $timestamp->format('M d, h:i A'),
                ];
            })
            ->values()
            ->all();

        $activeRewards = PointsStoreItem::query()
            ->where('parent_id', $parent->id)
            ->where('active', true)
            ->orderBy('points')
            ->orderBy('title')
            ->get();

        $pendingKeys = collect($pendingRewardRedemptions)
            ->map(fn (array $row) => $row['kid_id'].'|'.$row['item'])
            ->values()
            ->all();

        $offeredRewards = $kidModels
            ->flatMap(function (Kid $kid) use ($activeRewards, $pendingKeys) {
                return $activeRewards
                    ->filter(fn (PointsStoreItem $item) => (int) $kid->points >= (int) $item->points)
                    ->map(function (PointsStoreItem $item) use ($kid, $pendingKeys) {
                        $key = $kid->id.'|'.$item->title;

                        if (in_array($key, $pendingKeys, true)) {
                            return null;
                        }

                        return [
                            'status' => 'offered',
                            'kid' => (string) $kid->name,
                            'item' => (string) $item->title,
                            'points' => (int) $item->points,
                            'redeemed_at' => null,
                            'id' => null,
                        ];
                    })
                    ->filter();
            })
            ->values()
            ->all();

        $redeemedRows = collect($pendingRewardRedemptions)
            ->map(fn (array $row) => [
                'status' => 'redeemed',
                'kid' => $row['kid'],
                'item' => $row['item'],
                'points' => null,
                'redeemed_at' => $row['redeemed_at'],
                'id' => $row['id'],
            ])
            ->values()
            ->all();

        $this->rewardStatuses = collect($redeemedRows)
            ->concat($offeredRewards)
            ->values()
            ->all();

        $logs = ActivityLog::query()
            ->with(['kid', 'task'])
            ->whereHas('kid', fn ($query) => $query->where('parent_id', $parent->id))
            ->orderByDesc('completed_at')
            ->orderByDesc('created_at')
            ->limit(400)
            ->get()
            ->map(function (ActivityLog $log) use ($timezone) {
                $timestamp = $this->displayTimestampFromLog($log, $timezone);
                $kid = $log->kid?->name ?? 'Unknown';
                $task = $log->task?->title ?? '-';
                $action = str_starts_with($log->action, 'completed:') ? 'Completed' : $log->action;

                if ($log->action === 'Daily Bonus') {
                    $task = 'Full Day Completion';
                    $action = 'Daily Bonus (+10 pts)';
                }

                if (str_starts_with((string) $log->action, 'Redeemed Reward: ')) {
                    $task = str_replace('Redeemed Reward: ', '', (string) $log->action);
                    $action = 'Reward Redeemed';
                }

                if (str_starts_with((string) $log->action, 'Reward Fulfilled: #')) {
                    $task = trim((string) preg_replace('/^Reward Fulfilled: #\d+\s*/', '', (string) $log->action));
                    $action = 'Reward Fulfilled';
                }

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

    private function resolveDisplayTimezone(string $timezone): string
    {
        $candidate = trim($timezone);

        if ($candidate !== '' && in_array($candidate, DateTimeZone::listIdentifiers(), true)) {
            return $candidate;
        }

        $fallback = (string) config('app.timezone');

        if ($fallback !== '' && in_array($fallback, DateTimeZone::listIdentifiers(), true)) {
            return $fallback;
        }

        return 'UTC';
    }

    private function displayTimestampFromLog(ActivityLog $log, string $timezone): Carbon
    {
        $rawTimestamp = $log->getRawOriginal('completed_at') ?: $log->getRawOriginal('created_at');

        if (! is_string($rawTimestamp) || trim($rawTimestamp) === '') {
            return now('UTC')->setTimezone($timezone);
        }

        return Carbon::parse($rawTimestamp, 'UTC')->setTimezone($timezone);
    }

    public function fulfillRewardRedemption(int $redemptionLogId): void
    {
        $redemptionLog = ActivityLog::query()
            ->with('kid')
            ->whereKey($redemptionLogId)
            ->where('action', 'like', 'Redeemed Reward:%')
            ->first();

        if (! $redemptionLog || (int) ($redemptionLog->kid?->parent_id ?? 0) !== $this->parentId) {
            $this->dispatch('toast', message: 'Reward redemption not found.', type: 'error');

            return;
        }

        $alreadyFulfilled = ActivityLog::query()
            ->where('kid_id', $redemptionLog->kid_id)
            ->where('action', 'like', 'Reward Fulfilled: #'.$redemptionLog->id.'%')
            ->exists();

        if ($alreadyFulfilled) {
            $this->dispatch('toast', message: 'Reward already fulfilled.', type: 'warning');

            return;
        }

        $itemTitle = str_replace('Redeemed Reward: ', '', (string) $redemptionLog->action);

        ActivityLog::query()->create([
            'kid_id' => (int) $redemptionLog->kid_id,
            'task_id' => null,
            'action' => 'Reward Fulfilled: #'.$redemptionLog->id.' '.$itemTitle,
            'completed_at' => now('UTC'),
        ]);

        $this->dispatch('toast', message: 'Marked reward as fulfilled.', type: 'success');
        $this->loadDashboard();
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
