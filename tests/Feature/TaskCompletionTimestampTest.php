<?php

namespace Tests\Feature;

use App\Models\Kid;
use App\Models\Streak;
use App\Models\StreakBonus;
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

    public function test_completed_task_applies_active_streak_bonus_percentage(): void
    {
        $parent = User::query()->create([
            'name' => 'Parent',
            'email' => 'parent-bonus@test.local',
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
            'share_code' => 'BONUS001',
            'points' => 0,
        ]);

        Streak::query()->create([
            'kid_id' => $kid->id,
            'current_streak' => 3,
            'longest_streak' => 3,
            'last_completed_date' => now()->subDay()->toDateString(),
        ]);

        StreakBonus::query()->create([
            'parent_id' => $parent->id,
            'title' => '10 Percent Bonus',
            'description' => 'Task points gain a 10% streak boost.',
            'image_path' => null,
            'day_target' => 3,
            'bonus_type' => StreakBonus::TYPE_10_PERCENT_BONUS,
        ]);

        $task = Task::query()->create([
            'title' => 'Math',
            'description' => 'Practice math for 20 minutes',
            'points' => 10,
            'category' => 'Study',
            'active' => true,
        ]);

        $completed = app(GamificationService::class)->completeTask($kid, $task, now('UTC'));

        $this->assertTrue($completed);

        $kid->refresh();
        $this->assertSame(11, (int) $kid->points);
    }
}
