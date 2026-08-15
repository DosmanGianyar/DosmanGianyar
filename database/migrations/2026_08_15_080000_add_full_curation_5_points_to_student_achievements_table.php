<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('student_achievements', function (Blueprint $table) {
            $table->boolean('is_curation')->default(false)->after('status');

            // Poin 1: Dokumen Standar Penyelenggaraan
            $table->json('doc_standard_checklist')->nullable()->after('is_curation');
            $table->string('doc_standard_file')->nullable()->after('doc_standard_checklist');
            $table->text('doc_standard_url')->nullable()->after('doc_standard_file');

            // Poin 2: Tingkatan Seleksi Ajang
            $table->string('selection_level')->nullable()->after('doc_standard_url');
            $table->string('selection_level_file')->nullable()->after('selection_level');
            $table->text('selection_level_url')->nullable()->after('selection_level_file');

            // Poin 3: Konsistensi Frekuensi Penyelenggaraan
            $table->string('frequency_consistency')->nullable()->after('selection_level_url');
            $table->string('frequency_consistency_file')->nullable()->after('frequency_consistency');
            $table->text('frequency_consistency_url')->nullable()->after('frequency_consistency_file');

            // Poin 4: Sarana Prasarana Ajang
            $table->string('infrastructure_type')->nullable()->after('frequency_consistency_url');
            $table->string('infrastructure_file')->nullable()->after('infrastructure_type');

            // Poin 5: Penghargaan dan Apresiasi
            $table->json('reward_types')->nullable()->after('infrastructure_file');
            $table->string('reward_certificate_file')->nullable()->after('reward_types');
            $table->string('reward_photo_file')->nullable()->after('reward_certificate_file');
            $table->string('reward_recap_file')->nullable()->after('reward_photo_file');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_achievements', function (Blueprint $table) {
            $table->dropColumn([
                'is_curation',
                'doc_standard_checklist',
                'doc_standard_file',
                'doc_standard_url',
                'selection_level',
                'selection_level_file',
                'selection_level_url',
                'frequency_consistency',
                'frequency_consistency_file',
                'frequency_consistency_url',
                'infrastructure_type',
                'infrastructure_file',
                'reward_types',
                'reward_certificate_file',
                'reward_photo_file',
                'reward_recap_file',
            ]);
        });
    }
};
