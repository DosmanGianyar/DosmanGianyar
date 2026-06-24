<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_settings', function (Blueprint $table) {
            $table->id();
            $table->time('check_in_open')->default('06:00:00');  // absen masuk mulai bisa dilakukan
            $table->time('check_in_late')->default('07:30:00');  // batas hadir, lewat = terlambat
            $table->time('check_in_close')->default('08:00:00'); // batas akhir absen masuk, lewat = alpa
            $table->time('check_out_open')->default('13:00:00'); // paling cepat absen pulang
            $table->time('check_out_close')->nullable();         // batas akhir absen pulang (opsional)
            $table->timestamps();
        });

        // Insert one default row immediately
        DB::table('attendance_settings')->insert([
            'check_in_open'  => '06:00:00',
            'check_in_late'  => '07:30:00',
            'check_in_close' => '08:00:00',
            'check_out_open' => '13:00:00',
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_settings');
    }
};
