<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->time('check_in_time')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('photo')->nullable();       // path selfie
            $table->enum('status', ['hadir', 'terlambat', 'izin', 'sakit', 'alpa', 'dispensasi'])
                  ->default('alpa');
            $table->string('device_info')->nullable(); // UA string untuk audit
            $table->boolean('is_fake_gps')->default(false);
            $table->timestamps();

            $table->unique(['user_id', 'date']); // 1 record per siswa per hari
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
