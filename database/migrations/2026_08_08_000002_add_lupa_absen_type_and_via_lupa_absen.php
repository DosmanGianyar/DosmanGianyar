<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('forgot_attendance_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('forgot_attendance_requests', 'type')) {
                $table->string('type', 20)->default('masuk')->after('student_id');
            }
        });

        Schema::table('attendances', function (Blueprint $table) {
            if (!Schema::hasColumn('attendances', 'via_lupa_absen')) {
                $table->boolean('via_lupa_absen')->default(false)->after('status');
            }
            if (!Schema::hasColumn('attendances', 'lupa_absen_type')) {
                $table->string('lupa_absen_type', 20)->nullable()->after('via_lupa_absen');
            }
        });
    }

    public function down(): void
    {
        Schema::table('forgot_attendance_requests', function (Blueprint $table) {
            if (Schema::hasColumn('forgot_attendance_requests', 'type')) {
                $table->dropColumn('type');
            }
        });

        Schema::table('attendances', function (Blueprint $table) {
            if (Schema::hasColumn('attendances', 'via_lupa_absen')) {
                $table->dropColumn(['via_lupa_absen', 'lupa_absen_type']);
            }
        });
    }
};
