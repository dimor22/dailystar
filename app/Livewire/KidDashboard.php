<?php

namespace App\Livewire;

use App\Models\Kid;
use App\Models\TaskCompletion;
use App\Services\GamificationService;
use Livewire\Attributes\On;
use Livewire\Component;

class KidDashboard extends Component
{
    public int $kidId;

    public array $tasks = [];

    public string $kidName = '';

    public int $points = 0;

    public int $stars = 0;

    public int $currentStreak = 0;

    public int $completedCount = 0;

    public int $taskCount = 0;

    public bool $showCelebration = false;

    public function mount(?int $kidId = null): void
    {
        $resolvedKidId = $kidId ?? session('kid_id');

        abort_unless($resolvedKidId, 403);

        $this->kidId = (int) $resolvedKidId;
        $this->loadDashboard();
    }

    #[On('task-completed')]
    public function loadDashboard(): void
    {
        $kid = Kid::query()->with(['tasks', 'streak'])->findOrFail($this->kidId);
        $today = now()->toDateString();

        $completedTaskIds = TaskCompletion::query()
            ->where('kid_id', $kid->id)
            ->whereDate('completed_date', $today)
            ->pluck('task_id')
            ->all();

        $this->tasks = $kid->tasks
            ->map(function ($task) use ($completedTaskIds) {
                return [
                    'id' => $task->id,
                    'title' => $task->title,
                    'description' => $task->description,
                    'points' => $task->points,
                    'completed' => in_array($task->id, $completedTaskIds, true),
                ];
            })
            ->all();

        $this->kidName = $kid->name;
        $this->points = (int) $kid->points;
        $this->stars = app(GamificationService::class)->starsFromPoints($this->points);
        $this->currentStreak = (int) ($kid->streak->current_streak ?? 0);
        $this->taskCount = count($this->tasks);
        $this->completedCount = count(array_filter($this->tasks, fn (array $task) => $task['completed']));
        $this->showCelebration = $this->taskCount > 0 && $this->completedCount === $this->taskCount;
    }

    public function render()
    {
        return view('livewire.kid-dashboard');
    }
}
