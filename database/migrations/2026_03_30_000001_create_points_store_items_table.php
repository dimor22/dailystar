<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('points_store_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->unsignedInteger('points')->default(10);
            $table->string('image_path')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->index(['parent_id', 'active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('points_store_items');
    }
};
