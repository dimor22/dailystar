<?php

namespace Tests\Unit;

use App\Enums\Plan;
use App\Models\Kid;
use App\Models\KidTask;
use App\Models\Task;
use App\Models\User;
use App\Services\PlanGate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PlanGateTest extends TestCase
{
    use RefreshDatabase;

    private PlanGate $gate;

    protected function setUp(): void
    {
        parent::setUp();
        $this->gate = new PlanGate();
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    private function makeParent(): User
    {
        return User::query()->create([
            'name'     => 'Parent',
            'email'    => 'parent-' . uniqid() . '@test.local',
            'password' => Hash::make('password'),
            'role'     => 'parent',
            'timezone' => 'UTC',
        ]);
    }

    private function makeKid(User $parent): Kid
    {
        return Kid::query()->create([
            'parent_id' => $parent->id,
            'name'      => 'Kid',
            'avatar'    => '🦁',
            'color'     => 'bg-blue-500',
            'pin'       => '1234',
            'points'    => 0,
            'stars'     => 0,
        ]);
    }

    private function makeTask(User $parent): Task
    {
        return Task::query()->create([
            'parent_id'   => $parent->id,
            'title'       => 'Test Task',
            'description' => null,
            'points'      => 10,
            'category'    => 'Study',
            'active'      => true,
        ]);
    }

    private function assignTaskToKid(Kid $kid, Task $task): void
    {
        $order = ((int) KidTask::query()->where('kid_id', $kid->id)->max('order')) + 1;

        KidTask::query()->create([
            'kid_id'      => $kid->id,
            'task_id'     => $task->id,
            'order'       => $order,
            'active'      => true,
            'days_of_week' => ['monday'],
            'created_at'  => now(),
        ]);
    }

    // ─── planFor() ───────────────────────────────────────────────────────────

    public function test_plan_for_returns_free_when_no_subscription(): void
    {
        $user = $this->makeParent();

        $this->assertSame(Plan::Free, $this->gate->planFor($user));
    }

    // ─── canCreateKid() ──────────────────────────────────────────────────────

    public function test_free_user_can_create_kid_when_under_limit(): void
    {
        $user = $this->makeParent();

        $this->assertTrue($this->gate->canCreateKid($user));
    }

    public function test_free_user_can_create_second_kid(): void
    {
        $user = $this->makeParent();
        $this->makeKid($user);

        $this->assertTrue($this->gate->canCreateKid($user));
    }

    public function test_free_user_cannot_create_third_kid(): void
    {
        $user = $this->makeParent();
        $this->makeKid($user);
        $this->makeKid($user);

        $this->assertFalse($this->gate->canCreateKid($user));
    }

    public function test_free_plan_kid_limit_constant_is_two(): void
    {
        $this->assertSame(2, Plan::FREE_KID_LIMIT);
    }

    // ─── canAssignTask() ─────────────────────────────────────────────────────

    public function test_free_user_can_assign_task_when_under_limit(): void
    {
        $user = $this->makeParent();
        $kid  = $this->makeKid($user);

        $this->assertTrue($this->gate->canAssignTask($user, $kid));
    }

    public function test_free_user_cannot_assign_task_when_at_limit(): void
    {
        $user = $this->makeParent();
        $kid  = $this->makeKid($user);

        for ($i = 0; $i < Plan::FREE_TASK_LIMIT; $i++) {
            $this->assignTaskToKid($kid, $this->makeTask($user));
        }

        $this->assertFalse($this->gate->canAssignTask($user, $kid));
    }

    public function test_free_user_can_assign_task_up_to_but_not_exceeding_limit(): void
    {
        $user = $this->makeParent();
        $kid  = $this->makeKid($user);

        for ($i = 0; $i < Plan::FREE_TASK_LIMIT - 1; $i++) {
            $this->assignTaskToKid($kid, $this->makeTask($user));
        }

        $this->assertTrue($this->gate->canAssignTask($user, $kid));
    }

    // ─── hasFeature() ────────────────────────────────────────────────────────

    public function test_free_user_has_basic_tasks_feature(): void
    {
        $user = $this->makeParent();

        $this->assertTrue($this->gate->hasFeature($user, 'basic_tasks'));
    }

    public function test_free_user_does_not_have_streak_bonuses_feature(): void
    {
        $user = $this->makeParent();

        $this->assertFalse($this->gate->hasFeature($user, 'streak_bonuses'));
    }

    public function test_free_user_does_not_have_points_store_feature(): void
    {
        $user = $this->makeParent();

        $this->assertFalse($this->gate->hasFeature($user, 'points_store'));
    }

    // ─── kidLimitOverageFor() ────────────────────────────────────────────────

    public function test_no_overage_when_under_kid_limit(): void
    {
        $user = $this->makeParent();

        $this->assertSame(0, $this->gate->kidLimitOverageFor($user));
    }

    public function test_no_overage_at_exactly_kid_limit(): void
    {
        $user = $this->makeParent();
        $this->makeKid($user);
        $this->makeKid($user);

        $this->assertSame(0, $this->gate->kidLimitOverageFor($user));
    }

    public function test_overage_is_one_when_one_over_limit(): void
    {
        $user = $this->makeParent();
        $this->makeKid($user);
        $this->makeKid($user);
        $this->makeKid($user);

        $this->assertSame(1, $this->gate->kidLimitOverageFor($user));
    }
}
