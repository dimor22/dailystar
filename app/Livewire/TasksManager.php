<?php

namespace App\Livewire;

use App\Models\Kid;
use App\Models\KidTask;
use App\Models\Task;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class TasksManager extends Component
{
    public int $parentId = 0;

    public string $formTitle = '';

    public string $formDescription = '';

    public int $formPoints = 10;

    public string $formCategory = 'Study';

    public bool $formActive = true;

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
            $task = Task::query()->create([
                'title' => $validated['formTitle'],
                'description' => $validated['formDescription'] ?: null,
                'points' => $validated['formPoints'],
                'category' => $validated['formCategory'],
                'active' => $validated['formActive'],
            ]);

            $kids = Kid::query()->where('parent_id', $this->parentId)->get();

            foreach ($kids as $kid) {
                $nextOrder = ((int) KidTask::query()->where('kid_id', $kid->id)->max('order')) + 1;

                KidTask::query()->create([
                    'kid_id' => $kid->id,
                    'task_id' => $task->id,
                    'order' => $nextOrder,
                    'active' => true,
                    'created_at' => now(),
                ]);
            }
        });

        $this->resetForm();
    }

    public function editTask(int $taskId): void
    {
        $task = $this->ownedTasks()->findOrFail($taskId);

        $this->editingTaskId = $task->id;
        $this->formTitle = $task->title;
        $this->formDescription = (string) ($task->description ?? '');
        $this->formPoints = (int) $task->points;
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

        $task->update([
            'title' => $validated['formTitle'],
            'description' => $validated['formDescription'] ?: null,
            'points' => $validated['formPoints'],
            'category' => $validated['formCategory'],
            'active' => $validated['formActive'],
        ]);

        KidTask::query()
            ->where('task_id', $task->id)
            ->whereIn('kid_id', Kid::query()->where('parent_id', $this->parentId)->pluck('id'))
            ->update(['active' => (bool) $validated['formActive']]);

        $this->resetForm();
    }

    public function deleteTask(int $taskId): void
    {
        $task = $this->ownedTasks()->findOrFail($taskId);

        KidTask::query()
            ->where('task_id', $task->id)
            ->whereIn('kid_id', Kid::query()->where('parent_id', $this->parentId)->pluck('id'))
            ->delete();

        if (! KidTask::query()->where('task_id', $task->id)->exists()) {
            $task->delete();
        }

        if ($this->editingTaskId === $taskId) {
            $this->resetForm();
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
        return Task::query()->whereHas('kids', function ($query) {
            $query->where('parent_id', $this->parentId);
        });
    }

    private function rules(): array
    {
        return [
            'formTitle' => ['required', 'string', 'max:120'],
            'formDescription' => ['nullable', 'string', 'max:1000'],
            'formPoints' => ['required', 'integer', 'min:1', 'max:1000'],
            'formCategory' => ['required', 'string', 'max:100'],
            'formActive' => ['required', 'boolean'],
        ];
    }

    private function resetForm(): void
    {
        $this->editingTaskId = null;
        $this->formTitle = '';
        $this->formDescription = '';
        $this->formPoints = 10;
        $this->formCategory = 'Study';
        $this->formActive = true;
        $this->resetErrorBag();
    }
}
