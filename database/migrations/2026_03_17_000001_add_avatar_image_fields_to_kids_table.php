<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('kids', function (Blueprint $table) {
            $table->string('avatar_image_path')->nullable()->after('avatar');
            $table->string('avatar_display_mode', 10)->default('emoji')->after('avatar_image_path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kids', function (Blueprint $table) {
            $table->dropColumn(['avatar_image_path', 'avatar_display_mode']);
        });
    }
};
