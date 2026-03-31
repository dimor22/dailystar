<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('star_rewards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('image_path')->nullable();
            $table->boolean('active')->default(true);
            $table->unsignedInteger('order_number')->default(1);
            $table->unsignedInteger('stars_needed')->default(1);
            $table->timestamps();

            $table->index(['parent_id', 'order_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('star_rewards');
    }
};
