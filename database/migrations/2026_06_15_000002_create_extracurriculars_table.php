<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('extracurriculars', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('logo')->nullable();          // path di public disk
            $table->foreignId('pembina_id')              // guru pembina
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();
            $table->unsignedSmallInteger('max_members')->nullable(); // null = unlimited
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('extracurriculars');
    }
};
