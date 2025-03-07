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
        Schema::table('prayer_conference', function (Blueprint $table) {
            $table->timestamp('subscribed_at')->nullable();
        });

        DB::table('prayer_conference')
            ->whereNotNull('kc_user_id')
            ->update([
                'subscribed_at' => now()
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('prayer_conference', function (Blueprint $table) {
            $table->dropColumn('subscribed_at');
        });
    }
};
