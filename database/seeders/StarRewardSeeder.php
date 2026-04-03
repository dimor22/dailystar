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
            ['title' => 'Level 1 Gummy Bear', 'description' => 'Your first sweet star milestone.', 'order_number' => 1, 'stars_needed' => 1, 'image_path' => 'star-rewards/Level 1 Gummy Bear.png', 'active' => true],
            ['title' => 'Level 2 Hard Candy Swirl', 'description' => 'You are building momentum.', 'order_number' => 2, 'stars_needed' => 2, 'image_path' => 'star-rewards/Level 2 Hard Candy Swirl.png', 'active' => true],
            ['title' => 'Level 3 Lollipop', 'description' => 'Consistency is becoming a habit.', 'order_number' => 3, 'stars_needed' => 3, 'image_path' => 'star-rewards/Level 3 Lollipop.png', 'active' => true],
            ['title' => 'Level 4 Taffy Pull', 'description' => 'Great effort day after day.', 'order_number' => 4, 'stars_needed' => 5, 'image_path' => 'star-rewards/Level 4 Taffy Pull.png', 'active' => true],
            ['title' => 'Level 5 Jelly Bean', 'description' => 'Halfway to candy legend status.', 'order_number' => 5, 'stars_needed' => 7, 'image_path' => 'star-rewards/Level 5 Jelly Bean.png', 'active' => true],
            ['title' => 'Level 6 Candy Corn', 'description' => 'Your streak of effort keeps growing.', 'order_number' => 6, 'stars_needed' => 10, 'image_path' => 'star-rewards/Level 6 Candy Corn.png', 'active' => true],
            ['title' => 'Level 7 Gumball Machine', 'description' => 'Big progress unlocked.', 'order_number' => 7, 'stars_needed' => 14, 'image_path' => 'star-rewards/Level 7 Gumball Machine.png', 'active' => true],
            ['title' => 'Level 8 Cotton Candy', 'description' => 'You are floating on consistency.', 'order_number' => 8, 'stars_needed' => 19, 'image_path' => 'star-rewards/Level 8Cotton Candy.png', 'active' => true],
            ['title' => 'Level 9 Chocolate Bar', 'description' => 'Almost at the top tier.', 'order_number' => 9, 'stars_needed' => 25, 'image_path' => 'star-rewards/Level 9 Chocolate Bar.png', 'active' => true],
            ['title' => 'Level 10 Rock Candy', 'description' => 'Top candy champion unlocked.', 'order_number' => 10, 'stars_needed' => 32, 'image_path' => 'star-rewards/Level 10 Rock Candy.png', 'active' => true],
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
