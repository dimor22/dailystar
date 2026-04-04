<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Kid;
use App\Models\KidTask;
use App\Models\Streak;
use App\Models\StreakBonus;
use App\Models\Task;
use App\Models\TaskCompletion;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;

class GamificationService
{
    private const FULL_DAY_BONUS_POINTS = 10;

    public function completeTask(Kid $kid, Task $task, ?CarbonInterface $timestamp = null): bool
    {
        return $this->completeTaskWithDetails($kid, $task, $timestamp)['completed'];
    }

    public function completeTaskWithDetails(Kid $kid, Task $task, ?CarbonInterface $timestamp = null): array
    {
        $timestamp = $timestamp ? Carbon::instance($timestamp) : now();
        $timestampUtc = $timestamp->copy()->utc();
        $completedDate = $timestamp->toDateString();

        $activeBonusType = $this->resolveActiveStreakBonusType($kid);
        $bonusPercent = StreakBonus::percentageForType($activeBonusType);
        $bonusPoints = (int) floor(((int) $task->points * $bonusPercent) / 100);
        $totalTaskPoints = (int) $task->points + $bonusPoints;

        $alreadyCompleted = TaskCompletion::query()
            ->where('kid_id', $kid->id)
            ->where('task_id', $task->id)
            ->whereDate('completed_date', $completedDate)
            ->exists();

        if ($alreadyCompleted) {
            return [
                'completed' => false,
                'bonus_type' => $activeBonusType,
                'bonus_type_key' => StreakBonus::keyForType($activeBonusType),
                'bonus_percent' => $bonusPercent,
                'bonus_points' => 0,
                'task_points' => (int) $task->points,
                'total_task_points' => 0,
            ];
        }

        TaskCompletion::query()->create([
            'kid_id' => $kid->id,
            'task_id' => $task->id,
            'completed_date' => $completedDate,
            'completed_at' => $timestampUtc,
        ]);

        $this->addPointsAndProgressStars($kid, $totalTaskPoints);

        ActivityLog::query()->create([
            'kid_id' => $kid->id,
            'task_id' => $task->id,
            'action' => "Completed",
            'completed_at' => $timestampUtc,
        ]);

        $this->awardFullDayBonusIfEligible($kid, $timestamp);

        $this->updateStreak($kid, $timestamp->toDateString());

        return [
            'completed' => true,
            'bonus_type' => $activeBonusType,
            'bonus_type_key' => StreakBonus::keyForType($activeBonusType),
            'bonus_percent' => $bonusPercent,
            'bonus_points' => $bonusPoints,
            'task_points' => (int) $task->points,
            'total_task_points' => $totalTaskPoints,
        ];
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

        $dayStartUtc = $timestamp->copy()->startOfDay()->utc();
        $dayEndUtc = $timestamp->copy()->endOfDay()->utc();

        $alreadyAwarded = ActivityLog::query()
            ->where('kid_id', $kid->id)
            ->where('action', 'Daily Bonus')
            ->whereBetween('completed_at', [$dayStartUtc, $dayEndUtc])
            ->exists();

        if ($alreadyAwarded) {
            return;
        }

        $this->addPointsAndProgressStars($kid, self::FULL_DAY_BONUS_POINTS);

        ActivityLog::query()->create([
            'kid_id' => $kid->id,
            'task_id' => null,
            'action' => 'Daily Bonus',
            'completed_at' => $timestamp->copy()->utc(),
        ]);
    }

    private function resolveActiveStreakBonusType(Kid $kid): int
    {
        $currentStreak = (int) (Streak::query()->where('kid_id', $kid->id)->value('current_streak') ?? 0);

        if ($currentStreak <= 0) {
            return StreakBonus::TYPE_NO_BONUS;
        }

        $bonusType = StreakBonus::query()
            ->where('parent_id', (int) $kid->parent_id)
            ->where('day_target', '<=', $currentStreak)
            ->orderByDesc('day_target')
            ->value('bonus_type');

        return is_numeric($bonusType)
            ? (int) $bonusType
            : StreakBonus::TYPE_NO_BONUS;
    }

    private function addPointsAndProgressStars(Kid $kid, int $pointsToAdd): void
    {
        if ($pointsToAdd <= 0) {
            return;
        }

        $oldPoints = (int) $kid->points;
        $newPoints = $oldPoints + $pointsToAdd;

        $oldStarsFromPoints = $this->starsFromPoints($oldPoints);
        $newStarsFromPoints = $this->starsFromPoints($newPoints);
        $starsToAdd = max(0, $newStarsFromPoints - $oldStarsFromPoints);

        $kid->points = $newPoints;

        if ($starsToAdd > 0) {
            $kid->stars = (int) $kid->stars + $starsToAdd;
        }

        $kid->save();
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
