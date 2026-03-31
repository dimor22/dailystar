<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('streak_bonuses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('image_path')->nullable();
            $table->unsignedInteger('day_target');
            $table->string('bonus_type');
            $table->timestamps();

            $table->index(['parent_id', 'day_target']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('streak_bonuses');
    }
};
