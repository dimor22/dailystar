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
            ['title' => 'Momentum Boost', 'description' => 'Give a small point increase for consistency.', 'day_target' => 3, 'bonus_type' => 'points_increase'],
            ['title' => 'Weekly Streak Chest', 'description' => 'Unlock a surprise reward chest.', 'day_target' => 7, 'bonus_type' => 'streak_chest'],
            ['title' => 'Big Celebration Day', 'description' => 'Play confetti + celebration message.', 'day_target' => 14, 'bonus_type' => 'bigger_celebration'],
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
