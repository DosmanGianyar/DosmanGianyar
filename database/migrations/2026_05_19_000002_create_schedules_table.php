<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_id')->constrained('classes')->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->foreignId('teacher_id')->nullable()->constrained('users')->nullOnDelete();
            $table->tinyInteger('day');          // 1=Senin … 5=Jumat
            $table->tinyInteger('period');       // Jam ke-1, ke-2, dst.
            $table->time('start_time');
            $table->time('end_time');
            $table->string('room', 50)->nullable();
            $table->string('academic_year', 9)->default('2025/2026');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('schedules');
    }
};
