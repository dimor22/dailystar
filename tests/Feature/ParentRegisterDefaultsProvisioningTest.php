<?php

namespace Tests\Feature;

use App\Livewire\ParentRegister;
use App\Models\PointsStoreItem;
use App\Models\StarReward;
use App\Models\StreakBonus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ParentRegisterDefaultsProvisioningTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_parent_gets_default_points_items_star_rewards_and_streak_bonuses(): void
    {
        Livewire::test(ParentRegister::class)
            ->set('name', 'New Parent')
            ->set('email', 'new-parent@test.local')
            ->set('timezone', 'UTC')
            ->set('password', 'password123')
            ->set('passwordConfirmation', 'password123')
            ->call('register');

        $this->assertDatabaseHas('users', [
            'email' => 'new-parent@test.local',
            'role' => 'parent',
        ]);

        $parentId = (int) $this->app['db']->table('users')->where('email', 'new-parent@test.local')->value('id');

        $this->assertSame(4, PointsStoreItem::query()->where('parent_id', $parentId)->count());
        $this->assertSame(10, StarReward::query()->where('parent_id', $parentId)->count());
        $this->assertSame(5, StreakBonus::query()->where('parent_id', $parentId)->count());

        $this->assertDatabaseHas('points_store_items', [
            'parent_id' => $parentId,
            'title' => 'Pick Dessert',
        ]);

        $this->assertDatabaseHas('star_rewards', [
            'parent_id' => $parentId,
            'title' => 'Level 10 Rock Candy',
        ]);

        $this->assertDatabaseHas('streak_bonuses', [
            'parent_id' => $parentId,
            'title' => 'Legendary 21-Day Streak',
            'day_target' => 21,
        ]);
    }
}
