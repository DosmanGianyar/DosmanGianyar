<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_achievements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('achievement_categories')->nullOnDelete();
            $table->string('title', 200);
            $table->text('description')->nullable();
            $table->date('achievement_date');
            $table->enum('level', ['sekolah', 'kabupaten', 'provinsi', 'nasional', 'internasional']);
            $table->string('rank', 50)->nullable();   // "Juara 1", "Medali Emas", "Finalis"
            $table->string('photo')->nullable();       // foto kegiatan
            $table->string('certificate')->nullable(); // scan piagam
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();

            $table->index(['student_id', 'status']);
            $table->index(['achievement_date', 'level']);
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_achievements');
    }
};
