<?php

namespace App\Livewire;

use App\Models\Kid;
use App\Models\KidTask;
use App\Models\Task;
use Illuminate\Validation\Rule;
use Livewire\Component;

class KidsManager extends Component
{
    public int $parentId = 0;

    public string $formName = '';

    public string $formAvatar = '🦁';

    public string $formColor = 'bg-blue-500';

    public string $formPin = '';

    public array $assignedTaskIds = [];

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

        $this->syncKidTasks($kid, $validated['assignedTaskIds'] ?? []);

        $this->resetForm();
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

        return view('livewire.kids-manager', [
            'kids' => $this->ownedKids()->orderBy('name')->get(),
            'avatarOptions' => ['🦁', '🦄', '🚀', '🌈', '🐯', '🐼', '🦊', '🐙'],
            'colorOptions' => $colorOptions,
            'availableTasks' => $availableTasks,
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

    private function syncKidTasks(Kid $kid, array $selectedTaskIds): void
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
                ]);

                continue;
            }

            KidTask::query()->create([
                'kid_id' => $kid->id,
                'task_id' => $taskId,
                'order' => $index + 1,
                'active' => true,
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
        $this->resetErrorBag();
    }
}
