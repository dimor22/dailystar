<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('kids', function (Blueprint $table) {
            $table->string('public_id', 26)->nullable()->unique()->after('share_code');
        });

        $kidIds = DB::table('kids')->pluck('id');

        foreach ($kidIds as $kidId) {
            do {
                $publicId = (string) Str::ulid();
            } while (DB::table('kids')->where('public_id', $publicId)->exists());

            DB::table('kids')->where('id', $kidId)->update(['public_id' => $publicId]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kids', function (Blueprint $table) {
            $table->dropUnique(['public_id']);
            $table->dropColumn('public_id');
        });
    }
};
