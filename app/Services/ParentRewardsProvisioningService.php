<?php

namespace App\Services;

use App\Models\PointsStoreItem;
use App\Models\StarReward;
use App\Models\StreakBonus;
use App\Models\User;

class ParentRewardsProvisioningService
{
    public function provisionDefaults(User $parent): void
    {
        $this->provisionPointsStoreItems((int) $parent->id);
        $this->provisionStarRewards((int) $parent->id);
        $this->provisionStreakBonuses((int) $parent->id);
    }

    private function provisionPointsStoreItems(int $parentId): void
    {
        $items = [
            ['title' => 'Pick Dessert', 'description' => 'Choose tonight\'s dessert.', 'points' => 30, 'image_path' => 'points-store-items/pick-dessert.png', 'active' => true],
            ['title' => 'Extra Play Time', 'description' => '20 minutes extra play time.', 'points' => 50, 'image_path' => 'points-store-items/extra-play-time.png', 'active' => true],
            ['title' => 'Choose Family Game', 'description' => 'Pick the next family board game.', 'points' => 80, 'image_path' => 'points-store-items/family-game.png', 'active' => true],
            ['title' => 'Choose Weekend Activity', 'description' => 'Pick one family outing or activity.', 'points' => 120, 'image_path' => 'points-store-items/points-activities copy 2.png', 'active' => true],
        ];

        foreach ($items as $item) {
            PointsStoreItem::query()->updateOrCreate(
                [
                    'parent_id' => $parentId,
                    'title' => $item['title'],
                ],
                $item
            );
        }
    }

    private function provisionStarRewards(int $parentId): void
    {
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
                    'parent_id' => $parentId,
                    'title' => $reward['title'],
                ],
                $reward
            );
        }
    }

    private function provisionStreakBonuses(int $parentId): void
    {
        $bonuses = [
            [
                'title' => 'Great Start',
                'description' => 'You started your streak. Keep going!',
                'day_target' => 1,
                'bonus_type' => 'points_increase',
                'image_path' => 'streak-bonuses/1day-streak-badge.png',
            ],
            [
                'title' => 'Momentum Boost',
                'description' => 'Give a small point increase for consistency.',
                'day_target' => 3,
                'bonus_type' => 'points_increase',
                'image_path' => 'streak-bonuses/3day-streak-badge.png',
            ],
            [
                'title' => 'Weekly Streak Chest',
                'description' => 'Unlock a surprise reward chest.',
                'day_target' => 7,
                'bonus_type' => 'streak_chest',
                'image_path' => 'streak-bonuses/7day-streak-badge.png',
            ],
            [
                'title' => 'Big Celebration Day',
                'description' => 'Play confetti + celebration message.',
                'day_target' => 14,
                'bonus_type' => 'bigger_celebration',
                'image_path' => 'streak-bonuses/14day-streak-badge.png',
            ],
            [
                'title' => 'Legendary 21-Day Streak',
                'description' => 'Three full weeks of consistency. Legendary!',
                'day_target' => 21,
                'bonus_type' => 'bigger_celebration',
                'image_path' => 'streak-bonuses/21day-streak-badge.png',
            ],
        ];

        foreach ($bonuses as $bonus) {
            StreakBonus::query()->updateOrCreate(
                [
                    'parent_id' => $parentId,
                    'title' => $bonus['title'],
                ],
                $bonus
            );
        }
    }
}
