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
        Schema::create('homeroom_consultations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('class_id')->constrained('classes')->cascadeOnDelete();

            $table->string('topic');
            $table->text('student_note')->nullable();

            $table->enum('status', ['pending','scheduled','completed','cancelled'])->default('pending');
            $table->date('scheduled_date')->nullable();
            $table->date('conducted_date')->nullable();

            $table->text('teacher_note')->nullable();
            $table->text('follow_up')->nullable();
            $table->string('cancelled_reason')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('homeroom_consultations');
    }
};
