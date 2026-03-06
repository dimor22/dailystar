<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Kid;
use App\Models\Streak;
use App\Models\Task;
use App\Models\TaskCompletion;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;

class GamificationService
{
    public function completeTask(Kid $kid, Task $task, ?CarbonInterface $timestamp = null): bool
    {
        $timestamp = $timestamp ? Carbon::instance($timestamp) : now();
        $completedDate = $timestamp->toDateString();

        $alreadyCompleted = TaskCompletion::query()
            ->where('kid_id', $kid->id)
            ->where('task_id', $task->id)
            ->where('completed_date', $completedDate)
            ->exists();

        if ($alreadyCompleted) {
            return false;
        }

        TaskCompletion::query()->create([
            'kid_id' => $kid->id,
            'task_id' => $task->id,
            'completed_date' => $completedDate,
            'completed_at' => $timestamp,
        ]);

        $kid->increment('points', $task->points);

        ActivityLog::query()->create([
            'kid_id' => $kid->id,
            'task_id' => $task->id,
            'action' => "completed:{$task->title}",
        ]);

        $this->updateStreak($kid, $timestamp->toDateString());

        return true;
    }

    public function starsFromPoints(int $points): int
    {
        return (int) floor($points / 10);
    }

    private function updateStreak(Kid $kid, string $completedDate): void
    {
        /** @var Streak $streak */
        $streak = Streak::query()->firstOrCreate(
            ['kid_id' => $kid->id],
            [
                'current_streak' => 0,
                'longest_streak' => 0,
                'last_completed_date' => null,
            ]
        );

        $today = Carbon::parse($completedDate);
        $lastDate = $streak->last_completed_date ? Carbon::parse($streak->last_completed_date) : null;

        if (! $lastDate) {
            $streak->current_streak = 1;
        } elseif ($lastDate->isSameDay($today)) {
            return;
        } elseif ($lastDate->addDay()->isSameDay($today)) {
            $streak->current_streak++;
        } else {
            $streak->current_streak = 1;
        }

        $streak->longest_streak = max($streak->longest_streak, $streak->current_streak);
        $streak->last_completed_date = $today->toDateString();
        $streak->save();
    }
}
