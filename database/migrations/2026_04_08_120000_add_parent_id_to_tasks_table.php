<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('tasks', 'parent_id')) {
            Schema::table('tasks', function (Blueprint $table) {
                $table->foreignId('parent_id')->nullable()->after('id')->constrained('users')->nullOnDelete();
            });
        }

        DB::table('tasks')
            ->select('tasks.id as id')
            ->whereNull('tasks.parent_id')
            ->orderBy('id')
            ->chunkById(200, function ($tasks): void {
                foreach ($tasks as $task) {
                    $parentId = DB::table('kid_tasks')
                        ->join('kids', 'kids.id', '=', 'kid_tasks.kid_id')
                        ->where('kid_tasks.task_id', $task->id)
                        ->value('kids.parent_id');

                    if ($parentId) {
                        DB::table('tasks')
                            ->where('id', $task->id)
                            ->update(['parent_id' => $parentId]);
                    }
                }
            }, 'id');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('tasks', 'parent_id')) {
            Schema::table('tasks', function (Blueprint $table) {
                $table->dropConstrainedForeignId('parent_id');
            });
        }
    }
};
