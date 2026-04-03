<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            ParentAccountSeeder::class,
            KidsSeeder::class,
            TasksSeeder::class,
            KidTaskSeeder::class,
            StreakSeeder::class,
            PointsStoreItemSeeder::class,
            StarRewardSeeder::class,
            StreakBonusSeeder::class,
        ]);
    }
}
