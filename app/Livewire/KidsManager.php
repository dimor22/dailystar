<?php

namespace App\Livewire;

use App\Models\ActivityLog;
use App\Models\Kid;
use App\Models\KidTask;
use App\Models\Task;
use App\Models\TaskCompletion;
use Illuminate\Validation\Rule;
use Livewire\Component;

class KidsManager extends Component
{
    private const WEEK_DAYS = [
        'monday',
        'tuesday',
        'wednesday',
        'thursday',
        'friday',
        'saturday',
        'sunday',
    ];

    private const DEFAULT_WEEK_DAYS = [
        'monday',
        'tuesday',
        'wednesday',
        'thursday',
        'friday',
    ];

    public int $parentId = 0;

    public string $formName = '';

    public string $formAvatar = '🦁';

    public string $formColor = 'bg-blue-500';

    public string $formPin = '';

    public array $assignedTaskIds = [];

    public array $assignedTaskDays = [];

    public ?int $editingKidId = null;

    public function mount(): void
    {
        $this->parentId = (int) session('parent_user_id');

        $this->resetForm();
    }

    public function createKid(): void
    {
        if ($this->parentId <= 0) {
            return;
        }

        $validated = $this->validate($this->rules());

        Kid::query()->create([
            'parent_id' => $this->parentId,
            'name' => $validated['formName'],
            'avatar' => $validated['formAvatar'],
            'color' => $validated['formColor'],
            'pin' => $validated['formPin'],
            'points' => 0,
        ]);

        $this->resetForm();
    }

    public function editKid(int $kidId): void
    {
        $kid = $this->ownedKids()->findOrFail($kidId);

        $this->editingKidId = $kid->id;
        $this->formName = $kid->name;
        $this->formAvatar = $kid->avatar;
        $this->formColor = $kid->color;
        $this->formPin = $kid->getRawOriginal('pin');
        $this->assignedTaskIds = KidTask::query()
            ->where('kid_id', $kid->id)
            ->where('active', true)
            ->orderBy('order')
            ->pluck('task_id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $this->assignedTaskDays = KidTask::query()
            ->where('kid_id', $kid->id)
            ->where('active', true)
            ->orderBy('order')
            ->get(['task_id', 'days_of_week'])
            ->mapWithKeys(fn (KidTask $kidTask) => [
                (int) $kidTask->task_id => $this->weekDaysFromStoredValue($kidTask->days_of_week),
            ])
            ->all();

        $this->updatedAssignedTaskIds();
    }

    public function updateKid(): void
    {
        if (! $this->editingKidId) {
            return;
        }

        $kid = $this->ownedKids()->findOrFail($this->editingKidId);

        $validated = $this->validate($this->rules());

        $kid->update([
            'name' => $validated['formName'],
            'avatar' => $validated['formAvatar'],
            'color' => $validated['formColor'],
            'pin' => $validated['formPin'],
        ]);

        $taskIds = $validated['assignedTaskIds'] ?? [];
        $taskDays = $this->sanitizeAssignedTaskDays($taskIds);

        foreach ($taskIds as $taskId) {
            if (empty($taskDays[$taskId])) {
                $this->addError("assignedTaskDays.{$taskId}", 'Select at least one day for each assigned task.');

                return;
            }
        }

        $this->syncKidTasks($kid, $taskIds, $taskDays);

        $this->resetForm();
    }

    public function updatedAssignedTaskIds(): void
    {
        $selectedTaskIds = collect($this->assignedTaskIds)
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        $this->assignedTaskIds = $selectedTaskIds;

        $sanitizedDays = [];

        foreach ($selectedTaskIds as $taskId) {
            $existingDays = $this->assignedTaskDays[$taskId] ?? self::DEFAULT_WEEK_DAYS;
            $normalizedDays = $this->normalizeWeekDays($existingDays);

            $sanitizedDays[$taskId] = empty($normalizedDays)
                ? self::DEFAULT_WEEK_DAYS
                : $normalizedDays;
        }

        $this->assignedTaskDays = $sanitizedDays;
    }

    public function resetTaskForKid(int $taskId): void
    {
        if (! $this->editingKidId) {
            return;
        }

        $kid = $this->ownedKids()->findOrFail($this->editingKidId);

        $isAssigned = KidTask::query()
            ->where('kid_id', $kid->id)
            ->where('task_id', $taskId)
            ->where('active', true)
            ->exists();

        if (! $isAssigned) {
            return;
        }

        $task = Task::query()->findOrFail($taskId);

        $deletedCount = TaskCompletion::query()
            ->where('kid_id', $kid->id)
            ->where('task_id', $task->id)
            ->whereDate('completed_date', now()->toDateString())
            ->delete();

        if ($deletedCount > 0) {
            $today = now()->toDateString();

            $activityLogIds = ActivityLog::query()
                ->where('kid_id', $kid->id)
                ->where('task_id', $task->id)
                ->where('action', 'Completed')
                ->where(function ($query) use ($today) {
                    $query
                        ->whereDate('completed_at', $today)
                        ->orWhere(function ($innerQuery) use ($today) {
                            $innerQuery
                                ->whereNull('completed_at')
                                ->whereDate('created_at', $today);
                        });
                })
                ->orderByDesc('completed_at')
                ->orderByDesc('created_at')
                ->limit($deletedCount)
                ->pluck('id')
                ->all();

            if (! empty($activityLogIds)) {
                ActivityLog::query()->whereIn('id', $activityLogIds)->delete();
            }

            ActivityLog::query()->create([
                'kid_id' => $kid->id,
                'task_id' => $task->id,
                'action' => 'Reset',
                'completed_at' => now(),
            ]);

            $updatedPoints = max(0, ((int) $kid->points) - ($task->points * $deletedCount));
            $kid->update(['points' => $updatedPoints]);
            session()->flash('reset_task_success', 'Task reset for today.');

            return;
        }

        session()->flash('reset_task_success', 'No completion found for today.');
    }

    public function deleteKid(int $kidId): void
    {
        $this->ownedKids()->whereKey($kidId)->delete();

        if ($this->editingKidId === $kidId) {
            $this->resetForm();
        }
    }

    public function cancelEdit(): void
    {
        $this->resetForm();
    }

    public function render()
    {
        $colorOptions = $this->colorOptions();
        $availableTasks = $this->availableTasks();

        $kids = $this->ownedKids()
            ->with(['tasks'])
            ->orderBy('name')
            ->get();

        $kidSchedules = $kids->mapWithKeys(function (Kid $kid) {
            return [
                $kid->id => $kid->tasks->map(function ($task) {
                    $days = $this->weekDaysFromStoredValue($task->pivot->days_of_week ?? []);

                    return [
                        'task' => (string) $task->title,
                        'days' => $this->weekDaysLabel($days),
                    ];
                })->all(),
            ];
        })->all();

        return view('livewire.kids-manager', [
            'kids' => $kids,
            'avatarOptions' => ['🦁', '🦄', '🚀', '🌈', '🐯', '🐼', '🦊', '🐙','🌹','🏎️'],
            'colorOptions' => $colorOptions,
            'availableTasks' => $availableTasks,
            'weekDays' => self::WEEK_DAYS,
            'kidSchedules' => $kidSchedules,
        ]);
    }

    private function ownedKids()
    {
        return Kid::query()->where('parent_id', $this->parentId);
    }

    private function rules(): array
    {
        $allowedTaskIds = $this->availableTasks()->pluck('id')->all();

        $colorClasses = collect($this->colorOptions())
            ->pluck('class')
            ->all();

        return [
            'formName' => ['required', 'string', 'max:100'],
            'formAvatar' => ['required', 'string', 'max:10'],
            'formColor' => ['required', Rule::in($colorClasses)],
            'formPin' => ['required', 'digits:4'],
            'assignedTaskIds' => ['nullable', 'array'],
            'assignedTaskIds.*' => ['integer', Rule::in($allowedTaskIds)],
        ];
    }

    private function availableTasks()
    {
        return Task::query()
            ->whereHas('kids', function ($query) {
                $query->where('parent_id', $this->parentId);
            })
            ->orderBy('title')
            ->get(['id', 'title', 'points']);
    }

    private function syncKidTasks(Kid $kid, array $selectedTaskIds, array $selectedTaskDays): void
    {
        $taskIds = collect($selectedTaskIds)
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        $existingPivots = KidTask::query()
            ->where('kid_id', $kid->id)
            ->orderBy('order')
            ->get()
            ->keyBy('task_id');

        foreach ($taskIds as $index => $taskId) {
            $existing = $existingPivots->get($taskId);

            if ($existing) {
                $existing->update([
                    'order' => $index + 1,
                    'active' => true,
                    'days_of_week' => $selectedTaskDays[$taskId] ?? self::DEFAULT_WEEK_DAYS,
                ]);

                continue;
            }

            KidTask::query()->create([
                'kid_id' => $kid->id,
                'task_id' => $taskId,
                'order' => $index + 1,
                'active' => true,
                'days_of_week' => $selectedTaskDays[$taskId] ?? self::DEFAULT_WEEK_DAYS,
                'created_at' => now(),
            ]);
        }

        KidTask::query()
            ->where('kid_id', $kid->id)
            ->whereNotIn('task_id', $taskIds->all())
            ->delete();
    }

    private function colorOptions(): array
    {
        return [
            ['label' => 'Ocean Blue', 'class' => 'bg-blue-500'],
            ['label' => 'Bubblegum Pink', 'class' => 'bg-pink-500'],
            ['label' => 'Lime Green', 'class' => 'bg-green-500'],
            ['label' => 'Sunny Yellow', 'class' => 'bg-yellow-500'],
            ['label' => 'Grape Purple', 'class' => 'bg-purple-500'],
            ['label' => 'Tangerine Orange', 'class' => 'bg-orange-500'],
        ];
    }

    private function resetForm(): void
    {
        $this->editingKidId = null;
        $this->formName = '';
        $this->formAvatar = '🦁';
        $this->formColor = 'bg-blue-500';
        $this->formPin = '';
        $this->assignedTaskIds = [];
        $this->assignedTaskDays = [];
        $this->resetErrorBag();
    }

    private function sanitizeAssignedTaskDays(array $taskIds): array
    {
        $sanitizedTaskDays = [];

        foreach ($taskIds as $taskId) {
            $normalizedTaskId = (int) $taskId;
            $days = $this->assignedTaskDays[$normalizedTaskId] ?? [];
            $sanitizedTaskDays[$normalizedTaskId] = $this->normalizeWeekDays($days);
        }

        return $sanitizedTaskDays;
    }

    private function normalizeWeekDays(mixed $days): array
    {
        return collect((array) $days)
            ->map(fn ($day) => strtolower((string) $day))
            ->filter(fn ($day) => in_array($day, self::WEEK_DAYS, true))
            ->unique()
            ->values()
            ->all();
    }

    private function weekDaysFromStoredValue(mixed $days): array
    {
        $normalizedDays = $this->normalizeWeekDays($days);

        if (empty($normalizedDays)) {
            return self::WEEK_DAYS;
        }

        return $normalizedDays;
    }

    private function weekDaysLabel(array $days): string
    {
        $normalizedDays = $this->normalizeWeekDays($days);

        if (count($normalizedDays) === 7) {
            return 'Every day';
        }

        if ($normalizedDays === self::DEFAULT_WEEK_DAYS) {
            return 'Mon-Fri';
        }

        $map = [
            'monday' => 'Mon',
            'tuesday' => 'Tue',
            'wednesday' => 'Wed',
            'thursday' => 'Thu',
            'friday' => 'Fri',
            'saturday' => 'Sat',
            'sunday' => 'Sun',
        ];

        return collect($normalizedDays)
            ->map(fn (string $day) => $map[$day] ?? ucfirst(substr($day, 0, 3)))
            ->implode(', ');
    }
}
