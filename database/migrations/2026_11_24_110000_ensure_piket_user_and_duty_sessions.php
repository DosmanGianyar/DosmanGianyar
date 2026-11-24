<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('piket_sessions')) {
            Schema::create('piket_sessions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('account_user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
                $table->timestamp('started_at');
                $table->timestamp('ended_at')->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->string('user_agent')->nullable();
                $table->string('notes')->nullable();
                $table->timestamps();

                $table->index(['teacher_id', 'started_at']);
                $table->index(['account_user_id', 'ended_at']);
            });
        }

        User::firstOrCreate(
            ['email' => 'piket@sims.sch.id'],
            [
                'name'                 => 'Meja Piket SMAN 1 Gianyar',
                'nip'                  => 'piket',
                'password'             => Hash::make('Dosman123'),
                'role'                 => 'piket',
                'must_change_password' => false,
            ]
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('piket_sessions');
        User::where('email', 'piket@sims.sch.id')->delete();
    }
};
