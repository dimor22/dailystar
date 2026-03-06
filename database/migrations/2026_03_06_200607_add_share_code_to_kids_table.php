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
            $table->string('share_code', 12)->nullable()->unique()->after('pin');
        });

        $kidIds = DB::table('kids')->pluck('id');

        foreach ($kidIds as $kidId) {
            $code = null;

            do {
                $code = Str::upper(Str::random(8));
            } while (DB::table('kids')->where('share_code', $code)->exists());

            DB::table('kids')->where('id', $kidId)->update(['share_code' => $code]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kids', function (Blueprint $table) {
            $table->dropUnique(['share_code']);
            $table->dropColumn('share_code');
        });
    }
};
