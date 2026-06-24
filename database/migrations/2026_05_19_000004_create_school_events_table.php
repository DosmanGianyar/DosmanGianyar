<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('school_events', function (Blueprint $table) {
            $table->id();
            $table->string('title', 150);
            $table->text('description')->nullable();
            $table->date('event_date');
            $table->date('end_date')->nullable();
            $table->string('location', 150)->nullable();
            $table->string('cover_photo')->nullable();
            $table->string('type', 30)->default('kegiatan'); // kegiatan|lomba|rapat|upacara|wisuda|lainnya
            $table->boolean('is_published')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('school_events');
    }
};
