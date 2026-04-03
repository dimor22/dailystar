<?php

namespace Database\Seeders;

use App\Models\Kid;
use App\Models\KidTask;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Seeder;

class KidTaskSeeder extends Seeder
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
            ->orderBy('id')
            ->get();

        $tasks = Task::query()
            ->where('active', true)
            ->orderBy('id')
            ->get()
            ->values();

        foreach ($kids as $kid) {
            foreach ($tasks as $index => $task) {
                KidTask::query()->updateOrCreate(
                    ['kid_id' => $kid->id, 'task_id' => $task->id],
                    ['order' => $index + 1, 'active' => true, 'created_at' => now()]
                );
            }
        }
    }
}
