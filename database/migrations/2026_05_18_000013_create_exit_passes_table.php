<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exit_passes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->enum('reason', ['toilet', 'uks', 'other']);
            $table->string('reason_detail')->nullable();
            $table->timestamp('out_time');
            $table->timestamp('in_time')->nullable();
            $table->enum('status', ['out', 'returned'])->default('out');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exit_passes');
    }
};
