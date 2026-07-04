<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_devices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('device_id', 255);
            $table->timestamp('last_login_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'device_id']);
            $table->index('device_id');
        });

        // Migrasi data lama: pindahkan device_id dari users → user_devices
        DB::statement("
            INSERT IGNORE INTO user_devices (user_id, device_id, last_login_at, created_at, updated_at)
            SELECT id, device_id, device_locked_at, NOW(), NOW()
            FROM users
            WHERE device_id IS NOT NULL
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('user_devices');
    }
};
