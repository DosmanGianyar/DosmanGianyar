<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('extracurricular_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('extracurricular_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('role', ['member', 'ketua'])->default('member');
            $table->enum('status', ['pending_join', 'active', 'pending_leave'])->default('pending_join');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            $table->unique(['extracurricular_id', 'user_id']);
            $table->index(['extracurricular_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('extracurricular_members');
    }
};
