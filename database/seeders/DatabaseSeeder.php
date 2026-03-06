<?php

namespace Database\Seeders;

use App\Models\Kid;
use App\Models\KidTask;
use App\Models\Streak;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $parent = User::query()->updateOrCreate(
            ['email' => 'parent@dailystars.app'],
            [
                'name' => 'Parent Account',
                'password' => Hash::make('password'),
                'role' => 'parent',
                'timezone' => 'America/New_York',
            ]
        );

        $kids = collect([
            ['name' => 'Liam', 'avatar' => '🦁', 'color' => 'bg-blue-500', 'pin' => '1234'],
            ['name' => 'Emma', 'avatar' => '🦄', 'color' => 'bg-pink-500', 'pin' => '2222'],
            ['name' => 'Noah', 'avatar' => '🚀', 'color' => 'bg-green-500', 'pin' => '3333'],
            ['name' => 'Ava', 'avatar' => '🌈', 'color' => 'bg-yellow-500', 'pin' => '4444'],
        ])->map(function (array $kidData) use ($parent) {
            return Kid::query()->updateOrCreate(
                [
                    'parent_id' => $parent->id,
                    'name' => $kidData['name'],
                ],
                $kidData + ['points' => 0]
            );
        });

        $tasks = collect([
            ['title' => 'Math', 'description' => 'Solve 5 math problems', 'category' => 'Study', 'points' => 10],
            ['title' => 'Reading', 'description' => 'Read for 20 minutes', 'category' => 'Study', 'points' => 10],
            ['title' => 'Writing', 'description' => 'Write one journal paragraph', 'category' => 'Study', 'points' => 10],
            ['title' => 'Science', 'description' => 'Complete one science activity', 'category' => 'Study', 'points' => 15],
            ['title' => 'Scripture Study', 'description' => 'Read and discuss one verse', 'category' => 'Faith', 'points' => 20],
        ])->map(function (array $taskData) {
            return Task::query()->updateOrCreate(
                ['title' => $taskData['title']],
                $taskData + ['active' => true]
            );
        })->values();

        foreach ($kids as $kid) {
            foreach ($tasks as $index => $task) {
                KidTask::query()->updateOrCreate(
                    ['kid_id' => $kid->id, 'task_id' => $task->id],
                    ['order' => $index + 1, 'active' => true, 'created_at' => now()]
                );
            }

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
