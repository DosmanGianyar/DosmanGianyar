<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('parent_feature_seens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->string('feature', 32);
            $table->timestamp('last_seen_at')->nullable();
            $table->unsignedBigInteger('last_seen_id')->nullable();
            $table->timestamps();

            $table->unique(['parent_id', 'student_id', 'feature']);
            $table->index(['student_id', 'feature']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('parent_feature_seens');
    }
};

