<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Kid;
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

        $completed = $gamificationService->completeTask($kid, $task);

        if ($completed && $kid->parent) {
            $emailService->sendTaskCompletedNotification($kid->parent, $kid, $task);
        }

        $kid->refresh()->load('streak');

        return response()->json([
            'success' => $completed,
            'points' => $kid->points,
            'stars' => $gamificationService->starsFromPoints($kid->points),
            'streak' => $kid->streak?->current_streak ?? 0,
        ]);
    }
}
