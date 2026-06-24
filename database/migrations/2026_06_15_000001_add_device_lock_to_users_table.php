<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Flutter hardware Device ID (ANDROID_ID / identifierForVendor)
            $table->string('device_id', 255)->nullable()->after('nip');
            // Timestamp saat device pertama kali terikat ke akun ini
            $table->timestamp('device_locked_at')->nullable()->after('device_id');

            $table->index('device_id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['device_id']);
            $table->dropColumn(['device_id', 'device_locked_at']);
        });
    }
};
