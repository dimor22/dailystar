<?php

namespace Database\Seeders;

use App\Models\Task;
use Illuminate\Database\Seeder;

class TasksSeeder extends Seeder
{
    public function run(): void
    {
        $tasks = [
            ['title' => 'Math', 'description' => 'Solve 5 math problems', 'category' => 'Study', 'points' => 10],
            ['title' => 'Reading', 'description' => 'Read for 20 minutes', 'category' => 'Study', 'points' => 10],
            ['title' => 'Writing', 'description' => 'Write one journal paragraph', 'category' => 'Study', 'points' => 10],
            ['title' => 'Science', 'description' => 'Complete one science activity', 'category' => 'Study', 'points' => 15],
            ['title' => 'Scripture Study', 'description' => 'Read and discuss one verse', 'category' => 'Faith', 'points' => 20],
        ];

        foreach ($tasks as $taskData) {
            Task::query()->updateOrCreate(
                ['title' => $taskData['title']],
                $taskData + ['active' => true]
            );
        }
    }
}
