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
        $users = Illuminate\Support\Facades\DB::table('users')
            ->whereNotNull('device_id')
            ->get(['id', 'device_id', 'device_locked_at']);

        foreach ($users as $user) {
            Illuminate\Support\Facades\DB::table('user_devices')->insertOrIgnore([
                'user_id'       => $user->id,
                'device_id'     => $user->device_id,
                'last_login_at' => $user->device_locked_at,
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('user_devices');
    }
};
