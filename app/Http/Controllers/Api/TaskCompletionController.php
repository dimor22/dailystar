<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Kid;
use App\Models\KidTask;
use App\Models\Task;
use App\Services\EmailService;
use App\Services\GamificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TaskCompletionController extends Controller
{
    public function store(Request $request, GamificationService $gamificationService, EmailService $emailService): JsonResponse
    {
        $validated = $request->validate([
            'kid_id' => ['required', 'integer', 'exists:kids,id'],
            'task_id' => ['required', 'integer', 'exists:tasks,id'],
        ]);

        $kid = Kid::query()->with('parent')->findOrFail($validated['kid_id']);
        $task = Task::query()->findOrFail($validated['task_id']);

        $parentTimezone = (string) ($kid->parent?->timezone ?: config('app.timezone'));

        if (! in_array($parentTimezone, timezone_identifiers_list(), true)) {
            $parentTimezone = (string) config('app.timezone');
        }

        $timestamp = now()->setTimezone($parentTimezone);
        $todayWeekday = strtolower($timestamp->format('l'));

        $kidTask = KidTask::query()
            ->where('kid_id', $kid->id)
            ->where('task_id', $task->id)
            ->where('active', true)
            ->first();

        if (! $kidTask) {
            return response()->json([
                'success' => false,
                'message' => 'Task is not assigned to this kid.',
            ], 422);
        }

        $daysOfWeek = collect((array) ($kidTask->days_of_week ?? []))
            ->map(fn ($day) => strtolower((string) $day))
            ->values()
            ->all();

        if (! empty($daysOfWeek) && ! in_array($todayWeekday, $daysOfWeek, true)) {
            return response()->json([
                'success' => false,
                'message' => 'Task is not scheduled for today.',
            ], 422);
        }

        $completionResult = $gamificationService->completeTaskWithDetails(
            $kid,
            $task,
            $timestamp
        );

        $completed = (bool) ($completionResult['completed'] ?? false);

        if ($completed && $kid->parent) {
            $emailService->sendTaskCompletedNotification($kid->parent, $kid, $task);
        }

        $kid->refresh()->load('streak');

        return response()->json([
            'success' => $completed,
            'points' => $kid->points,
            'stars' => $gamificationService->starsFromPoints($kid->points),
            'streak' => $kid->streak?->current_streak ?? 0,
            'task_points' => (int) ($completionResult['task_points'] ?? 0),
            'bonus_type' => (int) ($completionResult['bonus_type'] ?? 0),
            'bonus_type_key' => (string) ($completionResult['bonus_type_key'] ?? 'no_bonus'),
            'bonus_percent' => (int) ($completionResult['bonus_percent'] ?? 0),
            'bonus_points' => (int) ($completionResult['bonus_points'] ?? 0),
            'total_task_points' => (int) ($completionResult['total_task_points'] ?? 0),
        ]);
    }
}
