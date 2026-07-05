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
        Schema::create('tujuan_pembelajaran', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('subject_id')->nullable()->constrained('subjects')->nullOnDelete();
            $table->string('code', 30)->nullable()->comment('Kode TP, misal: TP 1.1');
            $table->text('description')->comment('Deskripsi Tujuan Pembelajaran');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tujuan_pembelajaran');
    }
};
