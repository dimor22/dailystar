<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Kid;
use App\Models\KidTask;
use App\Models\Streak;
use App\Models\Task;
use App\Models\TaskCompletion;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;

class GamificationService
{
    private const FULL_DAY_BONUS_POINTS = 10;

    public function completeTask(Kid $kid, Task $task, ?CarbonInterface $timestamp = null): bool
    {
        $timestamp = $timestamp ? Carbon::instance($timestamp) : now();
        $completedDate = $timestamp->toDateString();

        $alreadyCompleted = TaskCompletion::query()
            ->where('kid_id', $kid->id)
            ->where('task_id', $task->id)
            ->whereDate('completed_date', $completedDate)
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
            'action' => "Completed",
            'completed_at' => $timestamp,
        ]);

        $this->awardFullDayBonusIfEligible($kid, $timestamp);

        $this->updateStreak($kid, $timestamp->toDateString());

        return true;
    }

    public function starsFromPoints(int $points): int
    {
        return (int) floor($points / 100);
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

        // Streak day counts only if every scheduled task for that day is completed.
        if (! $this->isFullyCompletedTaskDay($kid, $today)) {
            return;
        }

        $currentStreak = 0;
        $cursor = $today->copy();

        // Walk backward: skip no-task days, count full-completion days, stop at first missed task day.
        while (true) {
            $status = $this->taskDayStatus($kid, $cursor);

            if (! $status['has_tasks']) {
                $cursor->subDay();

                continue;
            }

            if (! $status['fully_completed']) {
                break;
            }

            $currentStreak++;
            $cursor->subDay();
        }

        $streak->current_streak = $currentStreak;
        $streak->longest_streak = max($streak->longest_streak, $streak->current_streak);
        $streak->last_completed_date = $today->toDateString();
        $streak->save();
    }

    private function awardFullDayBonusIfEligible(Kid $kid, Carbon $timestamp): void
    {
        if (! $this->isFullyCompletedTaskDay($kid, $timestamp)) {
            return;
        }

        $alreadyAwarded = ActivityLog::query()
            ->where('kid_id', $kid->id)
            ->where('action', 'Daily Bonus')
            ->whereDate('completed_at', $timestamp->toDateString())
            ->exists();

        if ($alreadyAwarded) {
            return;
        }

        $kid->increment('points', self::FULL_DAY_BONUS_POINTS);

        ActivityLog::query()->create([
            'kid_id' => $kid->id,
            'task_id' => null,
            'action' => 'Daily Bonus',
            'completed_at' => $timestamp,
        ]);
    }

    private function isFullyCompletedTaskDay(Kid $kid, Carbon $date): bool
    {
        $status = $this->taskDayStatus($kid, $date);

        return $status['has_tasks'] && $status['fully_completed'];
    }

    private function taskDayStatus(Kid $kid, Carbon $date): array
    {
        $weekday = strtolower($date->format('l'));
        $dateString = $date->toDateString();

        $scheduledTaskIds = KidTask::query()
            ->where('kid_id', $kid->id)
            ->where('active', true)
            ->where(function ($query) use ($weekday) {
                $query
                    ->whereNull('days_of_week')
                    ->orWhereJsonLength('days_of_week', 0)
                    ->orWhereJsonContains('days_of_week', $weekday);
            })
            ->pluck('task_id')
            ->map(fn ($taskId) => (int) $taskId)
            ->unique()
            ->values()
            ->all();

        if (count($scheduledTaskIds) === 0) {
            return [
                'has_tasks' => false,
                'fully_completed' => false,
            ];
        }

        $completedCount = TaskCompletion::query()
            ->where('kid_id', $kid->id)
            ->whereDate('completed_date', $dateString)
            ->whereIn('task_id', $scheduledTaskIds)
            ->distinct('task_id')
            ->count('task_id');

        return [
            'has_tasks' => true,
            'fully_completed' => $completedCount === count($scheduledTaskIds),
        ];
    }
}
