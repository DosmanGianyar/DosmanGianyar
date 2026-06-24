<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('school_regulations', function (Blueprint $table) {
            $table->id();
            $table->enum('category', ['kehadiran', 'berpakaian', 'perilaku', 'larangan']);
            $table->string('title', 200);
            $table->text('content');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['category', 'is_active', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('school_regulations');
    }
};
