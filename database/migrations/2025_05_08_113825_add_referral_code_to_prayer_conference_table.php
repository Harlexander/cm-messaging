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
            $table->string('referral_code')->nullable()->after('id');
            $table->integer('referral_count')->default(0)->after('referral_code');
        });

        // Copy data from prayer_conference_2_0
        DB::statement("
            UPDATE prayer_conference pc
            INNER JOIN prayer_conference_2_0 pc2 ON pc2.email = pc.email COLLATE latin1_swedish_ci
            SET 
                pc.referral_code = COALESCE(
                    NULLIF(pc2.referral_code, ''),
                    SUBSTRING(MD5(RAND()), 1, 8)
                ),
                pc.referral_count = COALESCE(pc2.referral_count, 0)
        ");

        // Generate codes for users not in prayer_conference_2_0
        DB::statement("
            UPDATE prayer_conference pc
            LEFT JOIN prayer_conference_2_0 pc2 ON pc2.email = pc.email COLLATE latin1_swedish_ci
            SET pc.referral_code = SUBSTRING(MD5(RAND()), 1, 8)
            WHERE pc2.email IS NULL OR pc.referral_code IS NULL
        ");

        // Make sure no nulls remain
        DB::statement("
            UPDATE prayer_conference
            SET referral_code = SUBSTRING(MD5(RAND()), 1, 8)
            WHERE referral_code IS NULL
        ");

        // Make the column required now that all rows have values
        Schema::table('prayer_conference', function (Blueprint $table) {
            $table->string('referral_code')->nullable(false)->change();
        });
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
