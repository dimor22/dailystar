<?php

namespace Tests\Feature;

use App\Livewire\KidDashboard;
use App\Models\Kid;
use App\Models\PointsStoreItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class KidRewardRedemptionTest extends TestCase
{
    use RefreshDatabase;

    public function test_kid_can_redeem_affordable_points_reward(): void
    {
        $parent = User::query()->create([
            'name' => 'Parent',
            'email' => 'parent-redeem@test.local',
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
            'points' => 120,
        ]);

        $reward = PointsStoreItem::query()->create([
            'parent_id' => $parent->id,
            'title' => 'Movie Night',
            'description' => 'Pick the family movie tonight.',
            'points' => 80,
            'active' => true,
        ]);

        $this->withSession([
            'parent_user_id' => $parent->id,
            'kid_id' => $kid->id,
        ]);

        Livewire::test(KidDashboard::class, ['kidId' => $kid->id])
            ->call('redeemPointsReward', $reward->id)
            ->assertSet('points', 40);

        $kid->refresh();

        $this->assertSame(40, (int) $kid->points);

        $this->assertDatabaseHas('activity_logs', [
            'kid_id' => $kid->id,
            'task_id' => null,
            'action' => 'Redeemed Reward: Movie Night',
        ]);
    }

    public function test_kid_cannot_redeem_unaffordable_points_reward(): void
    {
        $parent = User::query()->create([
            'name' => 'Parent',
            'email' => 'parent-no-redeem@test.local',
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
            'points' => 25,
        ]);

        $reward = PointsStoreItem::query()->create([
            'parent_id' => $parent->id,
            'title' => 'New Toy',
            'description' => 'Choose a toy from the store.',
            'points' => 50,
            'active' => true,
        ]);

        $this->withSession([
            'parent_user_id' => $parent->id,
            'kid_id' => $kid->id,
        ]);

        Livewire::test(KidDashboard::class, ['kidId' => $kid->id])
            ->call('redeemPointsReward', $reward->id)
            ->assertSet('points', 25);

        $kid->refresh();

        $this->assertSame(25, (int) $kid->points);

        $this->assertDatabaseMissing('activity_logs', [
            'kid_id' => $kid->id,
            'action' => 'Redeemed Reward: New Toy',
        ]);
    }
}
