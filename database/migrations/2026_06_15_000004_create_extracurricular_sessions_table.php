<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('extracurricular_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('extracurricular_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->date('session_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->string('location')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->boolean('is_open')->default(false);  // absen dibuka/ditutup ketua
            $table->timestamps();

            $table->index(['extracurricular_id', 'session_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('extracurricular_sessions');
    }
};
