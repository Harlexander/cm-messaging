<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First add the new columns
        Schema::table('prayer_conference', function (Blueprint $table) {
            $table->string('referral_code');
            $table->integer('referral_count')->default(0);
        });

        // Copy data from prayer_conference_2_0
        DB::statement("
            UPDATE prayer_conference pc
            INNER JOIN prayer_conference_2_0 pc2 ON pc2.email = pc.email
            SET 
                pc.referral_code = pc2.referral_code,
                pc.referral_count = pc2.referral_count
        ");

        // Generate referral codes for users who don't have one
        DB::statement("
            UPDATE prayer_conference
            SET referral_code = CONCAT(
                SUBSTRING(MD5(RAND()), 1, 8)
            )
            WHERE referral_code IS NULL
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('prayer_conference', function (Blueprint $table) {
            $table->dropColumn(['referral_code', 'referral_count']);
        });
    }
};
