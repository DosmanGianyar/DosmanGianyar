<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('extracurriculars')) {
            Schema::create('extracurriculars', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('contact_person')->nullable();
                $table->text('description')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('extracurricular_teachers')) {
            Schema::create('extracurricular_teachers', function (Blueprint $table) {
                $table->id();
                $table->foreignId('extracurricular_id')->constrained('extracurriculars')->cascadeOnDelete();
                $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
                $table->timestamps();

                $table->unique(['extracurricular_id', 'teacher_id']);
            });
        }

        if (! Schema::hasTable('extracurricular_students')) {
            Schema::create('extracurricular_students', function (Blueprint $table) {
                $table->id();
                $table->foreignId('extracurricular_id')->constrained('extracurriculars')->cascadeOnDelete();
                $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
                $table->enum('role', ['ketua', 'wakil_ketua', 'anggota'])->default('anggota');
                $table->timestamps();

                $table->unique(['extracurricular_id', 'student_id', 'role']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('extracurricular_students');
        Schema::dropIfExists('extracurricular_teachers');
        Schema::dropIfExists('extracurriculars');
    }
};
