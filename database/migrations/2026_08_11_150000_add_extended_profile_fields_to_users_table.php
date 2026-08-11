<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Profil Tambahan Siswa
            if (!Schema::hasColumn('users', 'hobbies')) {
                $table->string('hobbies', 255)->nullable()->after('address');
            }
            if (!Schema::hasColumn('users', 'aspirations')) {
                $table->string('aspirations', 255)->nullable()->after('hobbies');
            }
            if (!Schema::hasColumn('users', 'rt_rw')) {
                $table->string('rt_rw', 50)->nullable()->after('aspirations');
            }
            if (!Schema::hasColumn('users', 'kelurahan')) {
                $table->string('kelurahan', 100)->nullable()->after('rt_rw');
            }
            if (!Schema::hasColumn('users', 'kecamatan')) {
                $table->string('kecamatan', 100)->nullable()->after('kelurahan');
            }
            if (!Schema::hasColumn('users', 'kabupaten')) {
                $table->string('kabupaten', 100)->nullable()->after('kecamatan');
            }
            if (!Schema::hasColumn('users', 'residence_status')) {
                $table->string('residence_status', 50)->nullable()->after('kabupaten');
            }
            if (!Schema::hasColumn('users', 'transportation')) {
                $table->string('transportation', 50)->nullable()->after('residence_status');
            }
            if (!Schema::hasColumn('users', 'distance_km')) {
                $table->decimal('distance_km', 5, 2)->nullable()->after('transportation');
            }
            if (!Schema::hasColumn('users', 'travel_time_minutes')) {
                $table->integer('travel_time_minutes')->nullable()->after('distance_km');
            }

            // Orang Tua & Darurat
            if (!Schema::hasColumn('users', 'father_name')) {
                $table->string('father_name', 255)->nullable()->after('travel_time_minutes');
            }
            if (!Schema::hasColumn('users', 'father_phone')) {
                $table->string('father_phone', 50)->nullable()->after('father_name');
            }
            if (!Schema::hasColumn('users', 'father_job')) {
                $table->string('father_job', 100)->nullable()->after('father_phone');
            }
            if (!Schema::hasColumn('users', 'mother_name')) {
                $table->string('mother_name', 255)->nullable()->after('father_job');
            }
            if (!Schema::hasColumn('users', 'mother_phone')) {
                $table->string('mother_phone', 50)->nullable()->after('mother_name');
            }
            if (!Schema::hasColumn('users', 'mother_job')) {
                $table->string('mother_job', 100)->nullable()->after('mother_phone');
            }
            if (!Schema::hasColumn('users', 'guardian_name')) {
                $table->string('guardian_name', 255)->nullable()->after('mother_job');
            }
            if (!Schema::hasColumn('users', 'guardian_phone')) {
                $table->string('guardian_phone', 50)->nullable()->after('guardian_name');
            }
            if (!Schema::hasColumn('users', 'guardian_job')) {
                $table->string('guardian_job', 100)->nullable()->after('guardian_phone');
            }
            if (!Schema::hasColumn('users', 'emergency_contact_name')) {
                $table->string('emergency_contact_name', 255)->nullable()->after('guardian_job');
            }
            if (!Schema::hasColumn('users', 'emergency_contact_phone')) {
                $table->string('emergency_contact_phone', 50)->nullable()->after('emergency_contact_name');
            }
            if (!Schema::hasColumn('users', 'emergency_contact_relation')) {
                $table->string('emergency_contact_relation', 100)->nullable()->after('emergency_contact_phone');
            }

            // Kesehatan & Fisik
            if (!Schema::hasColumn('users', 'blood_type')) {
                $table->string('blood_type', 10)->nullable()->after('emergency_contact_relation');
            }
            if (!Schema::hasColumn('users', 'medical_history')) {
                $table->text('medical_history')->nullable()->after('blood_type');
            }
            if (!Schema::hasColumn('users', 'height_cm')) {
                $table->integer('height_cm')->nullable()->after('medical_history');
            }
            if (!Schema::hasColumn('users', 'weight_kg')) {
                $table->integer('weight_kg')->nullable()->after('height_cm');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'hobbies', 'aspirations', 'rt_rw', 'kelurahan', 'kecamatan', 'kabupaten',
                'residence_status', 'transportation', 'distance_km', 'travel_time_minutes',
                'father_name', 'father_phone', 'father_job',
                'mother_name', 'mother_phone', 'mother_job',
                'guardian_name', 'guardian_phone', 'guardian_job',
                'emergency_contact_name', 'emergency_contact_phone', 'emergency_contact_relation',
                'blood_type', 'medical_history', 'height_cm', 'weight_kg'
            ]);
        });
    }
};
