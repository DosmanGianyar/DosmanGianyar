<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dispensations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('requester_id')->constrained('users')->cascadeOnDelete(); // Guru pembina
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('activity_name');
            $table->date('date');
            $table->string('file')->nullable();        // SK kegiatan
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->timestamps();
        });

        Schema::create('dispensation_students', function (Blueprint $table) {
            $table->foreignId('dispensation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->primary(['dispensation_id', 'student_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dispensation_students');
        Schema::dropIfExists('dispensations');
    }
};
