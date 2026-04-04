<?php

namespace App\Livewire;

use App\Models\ActivityLog;
use App\Models\Kid;
use App\Models\KidTask;
use App\Models\Streak;
use App\Models\Task;
use App\Models\TaskCompletion;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;

class KidsManager extends Component
{
    use WithFileUploads;

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

    public string $formAvatarDisplayMode = 'emoji';

    public ?UploadedFile $formAvatarImage = null;

    public ?string $currentAvatarImagePath = null;

    public bool $removeCurrentAvatarImage = false;

    public string $formColor = 'bg-blue-500';

    public string $formPin = '';

    public array $assignedTaskIds = [];

    public array $assignedTaskDays = [];

    public array $completedTaskIdsToday = [];

    public ?int $editingKidId = null;

    public ?int $editingRewardsKidId = null;

    public string $editingRewardsKidName = '';

    public int $formRewardPoints = 0;

    public int $formRewardStars = 0;

    public int $formRewardStreakDays = 0;

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

        $avatarImagePath = $this->storeAvatarImageIfUploaded($this->formAvatarImage);
        $avatarDisplayMode = $this->resolveAvatarDisplayMode(
            $validated['formAvatarDisplayMode'] ?? 'emoji',
            $avatarImagePath
        );

        Kid::query()->create([
            'parent_id' => $this->parentId,
            'name' => $validated['formName'],
            'avatar' => $validated['formAvatar'],
            'avatar_image_path' => $avatarImagePath,
            'avatar_display_mode' => $avatarDisplayMode,
            'color' => $validated['formColor'],
            'pin' => $validated['formPin'],
            'points' => 0,
            'stars' => 0,
        ]);

        $this->resetForm();
        $this->dispatch('toast', message: 'Kid added.', type: 'success');
    }

    public function editKid(int $kidId): void
    {
        $kid = $this->ownedKids()->findOrFail($kidId);

        $this->editingKidId = $kid->id;
        $this->formName = $kid->name;
        $this->formAvatar = $kid->avatar;
        $this->formAvatarDisplayMode = (string) ($kid->avatar_display_mode ?: 'emoji');
        $this->currentAvatarImagePath = $kid->avatar_image_path;
        $this->removeCurrentAvatarImage = false;
        $this->formAvatarImage = null;
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

        $this->completedTaskIdsToday = TaskCompletion::query()
            ->where('kid_id', $kid->id)
            ->whereDate('completed_date', now()->toDateString())
            ->pluck('task_id')
            ->map(fn ($id) => (int) $id)
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

        $avatarImagePath = $this->storeAvatarImageIfUploaded($this->formAvatarImage);
        $shouldRemoveExistingImage = $this->removeCurrentAvatarImage;

        $activeAvatarImagePath = $avatarImagePath
            ?: ($shouldRemoveExistingImage ? null : $kid->avatar_image_path);
        $avatarDisplayMode = $this->resolveAvatarDisplayMode(
            $validated['formAvatarDisplayMode'] ?? 'emoji',
            $activeAvatarImagePath
        );

        if ($avatarImagePath && $kid->avatar_image_path) {
            Storage::disk('public')->delete($kid->avatar_image_path);
        }

        if ($shouldRemoveExistingImage && $kid->avatar_image_path && ! $avatarImagePath) {
            Storage::disk('public')->delete($kid->avatar_image_path);
        }

        $kid->update([
            'name' => $validated['formName'],
            'avatar' => $validated['formAvatar'],
            'avatar_image_path' => $activeAvatarImagePath,
            'avatar_display_mode' => $avatarDisplayMode,
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
        $this->dispatch('toast', message: 'Kid updated.', type: 'success');
    }

    public function updatedFormAvatarImage(): void
    {
        $this->validateOnly('formAvatarImage');

        if ($this->formAvatarImage) {
            $this->removeCurrentAvatarImage = false;
            $this->formAvatarDisplayMode = 'image';

            return;
        }

        if (! $this->currentAvatarImagePath) {
            $this->formAvatarDisplayMode = 'emoji';
        }
    }

    public function removeAvatarImage(): void
    {
        $this->formAvatarImage = null;

        if ($this->currentAvatarImagePath) {
            $this->removeCurrentAvatarImage = true;
            $this->currentAvatarImagePath = null;
        }

        $this->formAvatarDisplayMode = 'emoji';
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
                'completed_at' => now('UTC'),
            ]);

            $updatedPoints = max(0, ((int) $kid->points) - ($task->points * $deletedCount));
            $kid->update(['points' => $updatedPoints]);
            $this->completedTaskIdsToday = array_values(array_filter(
                $this->completedTaskIdsToday,
                fn (int $completedTaskId) => $completedTaskId !== (int) $task->id
            ));

            $this->dispatch('toast', message: 'Task reseted.', type: 'success');

            return;
        }

        $this->dispatch('toast', message: 'No completion found for today.', type: 'warning');
    }

    public function deleteKid(int $kidId): void
    {
        $kid = $this->ownedKids()->findOrFail($kidId);

        if ($kid->avatar_image_path) {
            Storage::disk('public')->delete($kid->avatar_image_path);
        }

        $this->ownedKids()->whereKey($kidId)->delete();

        if ($this->editingKidId === $kidId) {
            $this->resetForm();
        }

        $this->dispatch('toast', message: "{$kid->name} deleted.", type: 'success');
    }

    public function cancelEdit(): void
    {
        $this->resetForm();
    }

    public function loginAsKid(int $kidId): void
    {
        if (! $this->ownedKids()->whereKey($kidId)->exists()) {
            $this->dispatch('toast', message: 'Kid not found.', type: 'error');

            return;
        }

        // Parent shortcut must not be constrained by a prior shared-link session.
        session()->forget(['shared_kid_id', 'preselected_kid_id']);
        session()->put('kid_id', $kidId);

        $this->redirect(route('kid.login'));
    }

    public function openRewardsEditor(int $kidId): void
    {
        $kid = $this->ownedKids()->findOrFail($kidId);
        $currentStreak = (int) (Streak::query()->where('kid_id', $kid->id)->value('current_streak') ?? 0);

        $this->editingRewardsKidId = (int) $kid->id;
        $this->editingRewardsKidName = (string) $kid->name;
        $this->formRewardPoints = (int) $kid->points;
        $this->formRewardStars = (int) $kid->stars;
        $this->formRewardStreakDays = $currentStreak;
        $this->resetErrorBag();
    }

    public function saveRewardsEditor(): void
    {
        if (! $this->editingRewardsKidId) {
            return;
        }

        $validated = $this->validate([
            'formRewardPoints' => ['required', 'integer', 'min:0', 'max:1000000'],
            'formRewardStars' => ['required', 'integer', 'min:0', 'max:10000'],
            'formRewardStreakDays' => ['required', 'integer', 'min:0', 'max:3650'],
        ]);

        $kid = $this->ownedKids()->findOrFail($this->editingRewardsKidId);
        $kid->update([
            'points' => (int) $validated['formRewardPoints'],
            'stars' => (int) $validated['formRewardStars'],
        ]);

        $streak = Streak::query()->firstOrCreate(
            ['kid_id' => $kid->id],
            [
                'current_streak' => 0,
                'longest_streak' => 0,
                'last_completed_date' => null,
            ]
        );

        $newStreakDays = (int) $validated['formRewardStreakDays'];
        $streak->current_streak = $newStreakDays;
        $streak->longest_streak = max((int) $streak->longest_streak, $newStreakDays);
        $streak->last_completed_date = $newStreakDays > 0
            ? ($streak->last_completed_date ?: now()->toDateString())
            : null;
        $streak->save();

        $this->closeRewardsEditor();
        $this->dispatch('toast', message: 'Kid points/stars/streak updated.', type: 'success');
    }

    public function closeRewardsEditor(): void
    {
        $this->editingRewardsKidId = null;
        $this->editingRewardsKidName = '';
        $this->formRewardPoints = 0;
        $this->formRewardStars = 0;
        $this->formRewardStreakDays = 0;
        $this->resetErrorBag();
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

    protected function rules(): array
    {
        $allowedTaskIds = $this->availableTasks()->pluck('id')->all();

        $colorClasses = collect($this->colorOptions())
            ->pluck('class')
            ->all();

        return [
            'formName' => ['required', 'string', 'max:100'],
            'formAvatar' => ['required', 'string', 'max:10'],
            'formAvatarDisplayMode' => ['required', Rule::in(['emoji', 'image'])],
            'formAvatarImage' => ['nullable', 'mimetypes:image/jpeg,image/png,image/webp,image/avif', 'mimes:jpeg,jpg,png,webp,avif', 'max:1024'],
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
        $this->formAvatarDisplayMode = 'emoji';
        $this->formAvatarImage = null;
        $this->currentAvatarImagePath = null;
        $this->removeCurrentAvatarImage = false;
        $this->formColor = 'bg-blue-500';
        $this->formPin = '';
        $this->assignedTaskIds = [];
        $this->assignedTaskDays = [];
        $this->completedTaskIdsToday = [];
        $this->resetErrorBag();
    }

    private function storeAvatarImageIfUploaded(?UploadedFile $uploadedFile): ?string
    {
        if (! $uploadedFile) {
            return null;
        }

        return $uploadedFile->store('kid-avatars', 'public');
    }

    private function resolveAvatarDisplayMode(string $requestedMode, ?string $avatarImagePath): string
    {
        if (! $avatarImagePath) {
            return 'emoji';
        }

        return $requestedMode === 'image' ? 'image' : 'emoji';
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
        if (is_string($days)) {
            $decoded = json_decode($days, true);

            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $days = $decoded;
            } else {
                $days = [$days];
            }
        }

        return collect((array) $days)
            ->map(fn ($day) => strtolower((string) $day))
            ->filter(fn ($day) => in_array($day, self::WEEK_DAYS, true))
            ->unique()
            ->sortBy(fn (string $day) => array_search($day, self::WEEK_DAYS, true))
            ->values()
            ->all();
    }

    private function weekDaysFromStoredValue(mixed $days): array
    {
        $normalizedDays = $this->normalizeWeekDays($days);

        if (empty($normalizedDays)) {
            return self::DEFAULT_WEEK_DAYS;
        }

        return $normalizedDays;
    }

    private function weekDaysLabel(array $days): string
    {
        $normalizedDays = $this->normalizeWeekDays($days);

        if ($normalizedDays === self::WEEK_DAYS) {
            return 'Every day';
        }

        if ($normalizedDays === self::DEFAULT_WEEK_DAYS) {
            return 'Every week day';
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
