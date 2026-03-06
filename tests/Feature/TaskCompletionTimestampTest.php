<?php

namespace Tests\Feature;

use App\Models\Kid;
use App\Models\Task;
use App\Models\TaskCompletion;
use App\Models\User;
use App\Services\GamificationService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class TaskCompletionTimestampTest extends TestCase
{
    use RefreshDatabase;

    public function test_completed_task_saves_completion_time(): void
    {
        $parent = User::query()->create([
            'name' => 'Parent',
            'email' => 'parent-time@test.local',
            'password' => Hash::make('password'),
            'role' => 'parent',
            'timezone' => 'UTC',
        ]);

        $kid = Kid::query()->create([
            'parent_id' => $parent->id,
            'name' => 'Kid',
            'avatar' => '🦁',
            'color' => 'bg-blue-500',
            'pin' => '1234',
            'share_code' => 'TESTCODE',
            'points' => 0,
        ]);

        $task = Task::query()->create([
            'title' => 'Reading',
            'description' => 'Read for 20 minutes',
            'points' => 10,
            'category' => 'Study',
            'active' => true,
        ]);

        $completionTime = Carbon::parse('2026-03-06 14:35:20', 'UTC');

        $completed = app(GamificationService::class)->completeTask($kid, $task, $completionTime);

        $this->assertTrue($completed);

        $completion = TaskCompletion::query()->firstOrFail();

        $this->assertNotNull($completion->completed_at);
        $this->assertSame('2026-03-06 14:35:20', $completion->completed_at->format('Y-m-d H:i:s'));
    }
}
