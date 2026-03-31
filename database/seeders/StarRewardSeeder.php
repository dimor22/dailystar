<?php

namespace Database\Seeders;

use App\Models\StarReward;
use App\Models\User;
use Illuminate\Database\Seeder;

class StarRewardSeeder extends Seeder
{
    public function run(): void
    {
        $parent = User::query()
            ->where('role', 'parent')
            ->where('email', 'parent@dailystars.app')
            ->first();

        if (! $parent) {
            return;
        }

        $rewards = [
            ['title' => 'Rising Learner', 'description' => 'Reached your first star milestone.', 'order_number' => 1, 'stars_needed' => 1, 'active' => true],
            ['title' => 'Mission Maker', 'description' => 'Consistent effort is paying off.', 'order_number' => 2, 'stars_needed' => 3, 'active' => true],
            ['title' => 'Weekly Warrior', 'description' => 'Strong routine consistency!', 'order_number' => 3, 'stars_needed' => 5, 'active' => true],
            ['title' => 'Habit Hero', 'description' => 'Long-term progress unlocked.', 'order_number' => 4, 'stars_needed' => 8, 'active' => true],
        ];

        foreach ($rewards as $reward) {
            StarReward::query()->updateOrCreate(
                [
                    'parent_id' => $parent->id,
                    'title' => $reward['title'],
                ],
                $reward
            );
        }
    }
}
