<?php

namespace Database\Seeders;

use App\Models\Kid;
use App\Models\Streak;
use App\Models\User;
use Illuminate\Database\Seeder;

class StreakSeeder extends Seeder
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

        $kids = Kid::query()
            ->where('parent_id', $parent->id)
            ->get();

        foreach ($kids as $kid) {
            Streak::query()->updateOrCreate(
                ['kid_id' => $kid->id],
                [
                    'current_streak' => 0,
                    'longest_streak' => 0,
                    'last_completed_date' => null,
                    'updated_at' => now(),
                ]
            );
        }
    }
}
