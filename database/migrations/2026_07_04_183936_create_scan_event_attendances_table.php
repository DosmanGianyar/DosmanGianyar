<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scan_event_attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('scan_event_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('scanned_at');
            $table->foreignId('scanned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['scan_event_id', 'student_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scan_event_attendances');
    }
};
