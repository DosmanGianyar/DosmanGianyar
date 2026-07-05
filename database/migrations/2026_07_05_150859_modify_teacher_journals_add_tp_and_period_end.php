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
        Schema::table('teacher_journals', function (Blueprint $table) {
            // FK ke tujuan_pembelajaran (nullable agar tidak break data lama)
            $table->foreignId('tp_id')->nullable()->after('subject_id')
                  ->constrained('tujuan_pembelajaran')->nullOnDelete();
            // Jam pelajaran akhir (misal jam 1-3: period=1, period_end=3)
            $table->tinyInteger('period_end')->nullable()->after('period');
            // learning_objectives dibiarkan agar data lama tetap valid
            $table->text('learning_objectives')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('teacher_journals', function (Blueprint $table) {
            $table->dropForeign(['tp_id']);
            $table->dropColumn(['tp_id', 'period_end']);
            $table->text('learning_objectives')->nullable(false)->change();
        });
    }
};
