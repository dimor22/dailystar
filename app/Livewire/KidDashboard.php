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

    public int $parentId = 0;

    public array $tasks = [];

    public string $kidName = '';

    public int $points = 0;

    public int $stars = 0;

    public int $currentStreak = 0;

    public int $completedCount = 0;

    public int $taskCount = 0;

    public bool $showCelebration = false;

    public string $currentDate = '';

    public bool $hasLoadedOnce = false;

    public bool $wasAllTasksDone = false;

    public function mount(?int $kidId = null): void
    {
        $this->parentId = (int) session('parent_user_id');

        abort_unless($this->parentId > 0, 403);

        $resolvedKidId = $kidId ?? session('kid_id');

        abort_unless($resolvedKidId, 403);

        $this->kidId = (int) $resolvedKidId;
        $this->loadDashboard();
    }

    #[On('task-completed')]
    public function loadDashboard(): void
    {
        $kid = Kid::query()
            ->with(['tasks', 'streak'])
            ->where('parent_id', $this->parentId)
            ->findOrFail($this->kidId);
        $today = now()->toDateString();
        $this->currentDate = $today;

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

        $allTasksDone = $this->taskCount > 0 && $this->completedCount === $this->taskCount;
        $dismissedDate = (string) session("celebration_dismissed.{$kid->id}");
        $dismissedToday = $dismissedDate === $today;

        if (! $this->hasLoadedOnce) {
            $this->hasLoadedOnce = true;
            $this->wasAllTasksDone = $allTasksDone;
            $this->showCelebration = false;

            return;
        }

        if ($allTasksDone && ! $this->wasAllTasksDone && ! $dismissedToday) {
            $this->showCelebration = true;
        }

        if (! $allTasksDone) {
            $this->showCelebration = false;
        }

        $this->wasAllTasksDone = $allTasksDone;
    }

    #[On('celebration-dismissed')]
    public function dismissCelebration(): void
    {
        session()->put("celebration_dismissed.{$this->kidId}", $this->currentDate ?: now()->toDateString());
        $this->showCelebration = false;
    }

    public function render()
    {
        return view('livewire.kid-dashboard');
    }
}
