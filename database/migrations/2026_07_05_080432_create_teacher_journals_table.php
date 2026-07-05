<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teacher_journals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('class_id')->constrained('school_classes')->cascadeOnDelete();
            $table->foreignId('subject_id')->nullable()->constrained('subjects')->nullOnDelete();
            $table->date('date');
            $table->tinyInteger('period')->nullable()->comment('Jam ke-berapa');
            $table->text('learning_objectives')->comment('TP - Tujuan Pembelajaran');
            $table->text('material');
            $table->text('activity')->comment('Aktivitas pembelajaran');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('teacher_journal_absences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('journal_id')->constrained('teacher_journals')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->enum('status', ['tidak_hadir', 'izin', 'sakit'])->default('tidak_hadir');
            $table->unique(['journal_id', 'student_id']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teacher_journal_absences');
        Schema::dropIfExists('teacher_journals');
    }
};
