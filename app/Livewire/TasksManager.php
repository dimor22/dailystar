<?php

namespace App\Livewire;

use App\Models\Kid;
use App\Models\KidTask;
use App\Models\Task;
use App\Enums\Plan;
use App\Models\User;
use App\Services\PlanGate;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithFileUploads;

class TasksManager extends Component
{
    use WithFileUploads;

    private const ALLOWED_POINTS = [5, 10, 20];

    private const DEFAULT_WEEK_DAYS = [
        'monday',
        'tuesday',
        'wednesday',
        'thursday',
        'friday',
    ];

    public int $parentId = 0;

    public string $formTitle = '';

    public string $formDescription = '';

    public ?UploadedFile $formTaskImage = null;

    public ?string $currentTaskImagePath = null;

    public bool $removeCurrentTaskImage = false;

    public int $formPoints = 10;

    public string $formCategory = 'Study';

    public bool $formActive = true;

    public bool $assignToAllKidsOnCreate = false;

    public ?int $editingTaskId = null;

    public function mount(): void
    {
        $this->parentId = (int) session('parent_user_id');
        $this->resetForm();
    }

    public function createTask(): void
    {
        if ($this->parentId <= 0) {
            return;
        }

        $validated = $this->validate($this->rules());

        DB::transaction(function () use ($validated) {
            $taskImagePath = $this->storeTaskImageIfUploaded($this->formTaskImage);

            $task = Task::query()->create([
                'parent_id' => $this->parentId,
                'title' => $validated['formTitle'],
                'description' => $validated['formDescription'] ?: null,
                'image_path' => $taskImagePath,
                'points' => $validated['formPoints'],
                'category' => $validated['formCategory'],
                'active' => $validated['formActive'],
            ]);

            if ($this->assignToAllKidsOnCreate) {
                $user = User::find($this->parentId);
                $isFreePlan = $user && app(PlanGate::class)->planFor($user) === Plan::Free;

                $kids = Kid::query()->where('parent_id', $this->parentId)->get();

                foreach ($kids as $kid) {
                    // Skip kids that are already at the free-plan task limit.
                    if ($isFreePlan) {
                        $activeCount = KidTask::query()
                            ->where('kid_id', $kid->id)
                            ->where('active', true)
                            ->count();

                        if ($activeCount >= Plan::FREE_TASK_LIMIT) {
                            continue;
                        }
                    }
                    $nextOrder = ((int) KidTask::query()->where('kid_id', $kid->id)->max('order')) + 1;

                    KidTask::query()->create([
                        'kid_id' => $kid->id,
                        'task_id' => $task->id,
                        'order' => $nextOrder,
                        'active' => true,
                        'days_of_week' => self::DEFAULT_WEEK_DAYS,
                        'created_at' => now(),
                    ]);
                }
            }
        });

        $this->resetForm();
        $this->dispatch('toast', message: 'Task added.', type: 'success');
    }

    public function editTask(int $taskId): void
    {
        $task = $this->ownedTasks()->findOrFail($taskId);

        $this->editingTaskId = $task->id;
        $this->formTitle = $task->title;
        $this->formDescription = (string) ($task->description ?? '');
        $this->currentTaskImagePath = $task->image_path;
        $this->formTaskImage = null;
        $this->removeCurrentTaskImage = false;
        $taskPoints = (int) $task->points;
        $this->formPoints = collect(self::ALLOWED_POINTS)
            ->sortBy(fn (int $allowed) => abs($allowed - $taskPoints))
            ->first();
        $this->formCategory = (string) ($task->category ?? 'Study');
        $this->formActive = (bool) $task->active;
    }

    public function updateTask(): void
    {
        if (! $this->editingTaskId) {
            return;
        }

        $task = $this->ownedTasks()->findOrFail($this->editingTaskId);
        $validated = $this->validate($this->rules());

        $newTaskImagePath = $this->storeTaskImageIfUploaded($this->formTaskImage);
        $shouldRemoveExistingImage = $this->removeCurrentTaskImage;
        $activeTaskImagePath = $newTaskImagePath
            ?: ($shouldRemoveExistingImage ? null : $task->image_path);

        if ($newTaskImagePath && $task->image_path) {
            Storage::disk('public')->delete($task->image_path);
        }

        if ($shouldRemoveExistingImage && $task->image_path && ! $newTaskImagePath) {
            Storage::disk('public')->delete($task->image_path);
        }

        $task->update([
            'title' => $validated['formTitle'],
            'description' => $validated['formDescription'] ?: null,
            'image_path' => $activeTaskImagePath,
            'points' => $validated['formPoints'],
            'category' => $validated['formCategory'],
            'active' => $validated['formActive'],
        ]);

        KidTask::query()
            ->where('task_id', $task->id)
            ->whereIn('kid_id', Kid::query()->where('parent_id', $this->parentId)->pluck('id'))
            ->update(['active' => (bool) $validated['formActive']]);

        $this->resetForm();
        $this->dispatch('toast', message: 'Task updated.', type: 'success');
    }

    public function deleteTask(int $taskId): void
    {
        $task = $this->ownedTasks()->findOrFail($taskId);

        KidTask::query()
            ->where('task_id', $task->id)
            ->whereIn('kid_id', Kid::query()->where('parent_id', $this->parentId)->pluck('id'))
            ->delete();

        if (! KidTask::query()->where('task_id', $task->id)->exists()) {
            if ($task->image_path) {
                Storage::disk('public')->delete($task->image_path);
            }

            $task->delete();
        }

        if ($this->editingTaskId === $taskId) {
            $this->resetForm();
        }

        $this->dispatch('toast', message: 'Task deleted.', type: 'success');
    }

    public function updatedFormTaskImage(): void
    {
        $this->validateOnly('formTaskImage');

        if ($this->formTaskImage) {
            $this->removeCurrentTaskImage = false;
        }
    }

    public function removeTaskImage(): void
    {
        $this->formTaskImage = null;

        if ($this->currentTaskImagePath) {
            $this->removeCurrentTaskImage = true;
            $this->currentTaskImagePath = null;
        }
    }

    public function cancelEdit(): void
    {
        $this->resetForm();
    }

    public function render()
    {
        return view('livewire.tasks-manager', [
            'tasks' => $this->ownedTasks()->orderBy('title')->get(),
            'categoryOptions' => ['Study', 'Faith', 'Chores', 'Health', 'Other'],
        ]);
    }

    private function ownedTasks()
    {
        return Task::query()->where('parent_id', $this->parentId);
    }

    protected function rules(): array
    {
        return [
            'formTitle' => ['required', 'string', 'max:120'],
            'formDescription' => ['nullable', 'string', 'max:1000'],
            'formTaskImage' => ['nullable', 'mimetypes:image/jpeg,image/png,image/webp,image/avif', 'mimes:jpeg,jpg,png,webp,avif', 'max:1024'],
            'formPoints' => ['required', 'integer', 'in:5,10,20'],
            'formCategory' => ['required', 'string', 'max:100'],
            'formActive' => ['required', 'boolean'],
        ];
    }

    private function storeTaskImageIfUploaded(?UploadedFile $uploadedFile): ?string
    {
        if (! $uploadedFile) {
            return null;
        }

        return $uploadedFile->store('task-images', 'public');
    }

    private function resetForm(): void
    {
        $this->editingTaskId = null;
        $this->formTitle = '';
        $this->formDescription = '';
        $this->formTaskImage = null;
        $this->currentTaskImagePath = null;
        $this->removeCurrentTaskImage = false;
        $this->formPoints = 10;
        $this->formCategory = 'Study';
        $this->formActive = true;
        $this->assignToAllKidsOnCreate = false;
        $this->resetErrorBag();
    }
}
