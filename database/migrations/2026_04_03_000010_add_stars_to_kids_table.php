<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kids', function (Blueprint $table) {
            $table->unsignedInteger('stars')->default(0)->after('points');
        });

        DB::table('kids')
            ->select(['id', 'points'])
            ->orderBy('id')
            ->chunkById(200, function ($kids): void {
                foreach ($kids as $kid) {
                    DB::table('kids')
                        ->where('id', $kid->id)
                        ->update(['stars' => (int) floor(((int) $kid->points) / 100)]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('kids', function (Blueprint $table) {
            $table->dropColumn('stars');
        });
    }
};
