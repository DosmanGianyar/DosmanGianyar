<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('extracurricular_grades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('extracurricular_id')->constrained('extracurriculars')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('academic_year', 9);
            $table->unsignedTinyInteger('semester');
            $table->string('predicate', 10);
            $table->text('description')->nullable();
            $table->foreignId('graded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['extracurricular_id', 'user_id', 'academic_year', 'semester'], 'extra_grades_unique');
            $table->index(['user_id', 'academic_year', 'semester'], 'extra_grades_student_term_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('extracurricular_grades');
    }
};
