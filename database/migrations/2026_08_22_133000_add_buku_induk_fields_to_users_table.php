<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // A. Diri Siswa
            $table->string('nickname')->nullable()->after('name');
            $table->string('birth_place')->nullable()->after('birth_date');
            $table->string('religion')->nullable()->after('birth_place');
            $table->string('citizenship')->nullable()->default('WNI')->after('religion');
            $table->unsignedInteger('child_order')->nullable()->after('citizenship');
            $table->unsignedInteger('siblings_count')->nullable()->after('child_order');
            $table->unsignedInteger('step_siblings_count')->nullable()->after('siblings_count');
            $table->unsignedInteger('foster_siblings_count')->nullable()->after('step_siblings_count');
            $table->string('orphan_status')->nullable()->after('foster_siblings_count');
            $table->string('daily_language')->nullable()->after('orphan_status');

            // B & C. Tempat Tinggal & Kesehatan
            $table->string('living_with')->nullable()->after('address');
            $table->string('physical_disability')->nullable()->after('medical_history');

            // D. Pendidikan Sebelumnya & Penerimaan
            $table->string('prev_school_name')->nullable();
            $table->string('prev_sttb_no')->nullable();
            $table->date('prev_sttb_date')->nullable();
            $table->string('prev_study_duration')->nullable();
            $table->string('transfer_from_school')->nullable();
            $table->string('transfer_reason')->nullable();
            $table->string('admission_grade')->nullable();
            $table->string('admission_class_group')->nullable();
            $table->string('admission_major')->nullable();
            $table->date('admission_date')->nullable();

            // E. Ayah Kandung
            $table->string('father_birth_place')->nullable();
            $table->date('father_birth_date')->nullable();
            $table->string('father_religion')->nullable();
            $table->string('father_citizenship')->nullable();
            $table->string('father_education')->nullable();
            $table->string('father_income')->nullable();
            $table->text('father_address')->nullable();
            $table->string('father_status')->nullable();

            // F. Ibu Kandung
            $table->string('mother_birth_place')->nullable();
            $table->date('mother_birth_date')->nullable();
            $table->string('mother_religion')->nullable();
            $table->string('mother_citizenship')->nullable();
            $table->string('mother_education')->nullable();
            $table->string('mother_income')->nullable();
            $table->text('mother_address')->nullable();
            $table->string('mother_status')->nullable();

            // G. Wali
            $table->string('guardian_birth_place')->nullable();
            $table->date('guardian_birth_date')->nullable();
            $table->string('guardian_religion')->nullable();
            $table->string('guardian_citizenship')->nullable();
            $table->string('guardian_education')->nullable();
            $table->string('guardian_income')->nullable();
            $table->text('guardian_address')->nullable();
            $table->string('guardian_relation')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'nickname', 'birth_place', 'religion', 'citizenship', 'child_order',
                'siblings_count', 'step_siblings_count', 'foster_siblings_count',
                'orphan_status', 'daily_language', 'living_with', 'physical_disability',
                'prev_school_name', 'prev_sttb_no', 'prev_sttb_date', 'prev_study_duration',
                'transfer_from_school', 'transfer_reason', 'admission_grade',
                'admission_class_group', 'admission_major', 'admission_date',
                'father_birth_place', 'father_birth_date', 'father_religion', 'father_citizenship',
                'father_education', 'father_income', 'father_address', 'father_status',
                'mother_birth_place', 'mother_birth_date', 'mother_religion', 'mother_citizenship',
                'mother_education', 'mother_income', 'mother_address', 'mother_status',
                'guardian_birth_place', 'guardian_birth_date', 'guardian_religion', 'guardian_citizenship',
                'guardian_education', 'guardian_income', 'guardian_address', 'guardian_relation',
            ]);
        });
    }
};
