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
        Schema::create('session_attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_attendance_id')->nullable()->constrained('teacher_attendances')->nullOnDelete();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('class_id')->constrained('classes')->cascadeOnDelete();
            $table->foreignId('subject_id')->nullable()->constrained('subjects')->nullOnDelete();
            $table->date('date');
            $table->tinyInteger('period');
            $table->enum('status', ['hadir', 'tidak_hadir', 'izin', 'sakit'])->default('hadir');
            $table->text('note')->nullable();
            $table->timestamps();
            $table->unique(['student_id', 'date', 'period']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('session_attendances');
    }
};
