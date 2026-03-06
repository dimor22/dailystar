<?php

namespace App\Services;

use App\Models\Kid;
use App\Models\Task;
use App\Models\User;
use Throwable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class EmailService
{
    public function sendTaskCompletedNotification(User $parent, Kid $kid, Task $task): void
    {
        try {
            Mail::raw(
                "{$kid->name} completed {$task->title} and earned {$task->points} points!",
                fn ($message) => $message
                    ->to($parent->email)
                    ->subject('DailyStars Task Completed')
            );
        } catch (Throwable $exception) {
            Log::warning('Task completion email failed.', [
                'error' => $exception->getMessage(),
                'parent_id' => $parent->id,
                'kid_id' => $kid->id,
                'task_id' => $task->id,
            ]);
        }

        Log::info('Task completion email queued/sent.', [
            'parent_id' => $parent->id,
            'kid_id' => $kid->id,
            'task_id' => $task->id,
        ]);
    }
}
