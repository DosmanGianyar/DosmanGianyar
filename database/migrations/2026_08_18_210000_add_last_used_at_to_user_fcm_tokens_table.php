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
        if (Schema::hasTable('user_fcm_tokens') && ! Schema::hasColumn('user_fcm_tokens', 'last_used_at')) {
            Schema::table('user_fcm_tokens', function (Blueprint $table) {
                $table->timestamp('last_used_at')->nullable()->after('device_name');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('user_fcm_tokens') && Schema::hasColumn('user_fcm_tokens', 'last_used_at')) {
            Schema::table('user_fcm_tokens', function (Blueprint $table) {
                $table->dropColumn('last_used_at');
            });
        }
    }
};
