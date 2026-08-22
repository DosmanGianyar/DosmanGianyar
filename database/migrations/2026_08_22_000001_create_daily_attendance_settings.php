<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendance_settings', function (Blueprint $table) {
            if (! Schema::hasColumn('attendance_settings', 'day_of_week')) {
                $table->unsignedTinyInteger('day_of_week')->nullable()->after('id');
            }
            if (! Schema::hasColumn('attendance_settings', 'day_name')) {
                $table->string('day_name', 20)->nullable()->after('day_of_week');
            }
            if (! Schema::hasColumn('attendance_settings', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('check_out_close');
            }
        });

        // Hapus data lama agar terhindar dari bentrokan ID/setting global lama
        DB::table('attendance_settings')->truncate();

        // Seed 7 hari presensi
        $days = [
            1 => ['name' => 'Senin',  'check_out' => '13:30:00', 'active' => true],
            2 => ['name' => 'Selasa', 'check_out' => '13:30:00', 'active' => true],
            3 => ['name' => 'Rabu',   'check_out' => '13:30:00', 'active' => true],
            4 => ['name' => 'Kamis',  'check_out' => '13:30:00', 'active' => true],
            5 => ['name' => 'Jumat',  'check_out' => '13:30:00', 'active' => true],
            6 => ['name' => 'Sabtu',  'check_out' => '11:00:00', 'active' => true],
            7 => ['name' => 'Minggu', 'check_out' => '12:00:00', 'active' => false],
        ];

        foreach ($days as $num => $day) {
            DB::table('attendance_settings')->insert([
                'day_of_week'    => $num,
                'day_name'       => $day['name'],
                'check_in_open'  => '05:00:00',
                'check_in_late'  => '07:15:00',
                'check_in_close' => '08:00:00',
                'check_out_open' => $day['check_out'],
                'check_out_close'=> null,
                'is_active'      => $day['active'],
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('attendance_settings', function (Blueprint $table) {
            if (Schema::hasColumn('attendance_settings', 'day_of_week')) {
                $table->dropColumn(['day_of_week', 'day_name', 'is_active']);
            }
        });
    }
};
