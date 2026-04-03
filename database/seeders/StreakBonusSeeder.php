<?php

namespace Database\Seeders;

use App\Models\StreakBonus;
use App\Models\User;
use Illuminate\Database\Seeder;

class StreakBonusSeeder extends Seeder
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
                    'parent_id' => $parent->id,
                    'title' => $bonus['title'],
                ],
                $bonus
            );
        }
    }
}
