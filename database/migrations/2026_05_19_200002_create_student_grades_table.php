<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_grades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->decimal('score', 5, 2);
            $table->string('type', 10)->default('UH'); // UH|UTS|UAS
            $table->tinyInteger('semester')->default(1); // 1 atau 2
            $table->string('academic_year', 9);         // contoh: 2025/2026
            $table->string('notes', 200)->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['student_id', 'academic_year', 'semester']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_grades');
    }
};
