<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Drop unique constraint on holidays.date so multiple class-specific
        // entries can exist for the same date.
        Schema::table('holidays', function (Blueprint $table) {
            $table->dropUnique(['date']);
            $table->enum('type', ['libur', 'sekolah_khusus'])
                ->default('libur')
                ->after('description');
            $table->enum('applies_to', ['semua', 'kelas_tertentu'])
                ->default('semua')
                ->after('type');
        });

        // Pivot: holiday <-> school class (used when applies_to = kelas_tertentu)
        Schema::create('holiday_class', function (Blueprint $table) {
            $table->id();
            $table->foreignId('holiday_id')->constrained()->cascadeOnDelete();
            $table->foreignId('school_class_id')->constrained('classes')->cascadeOnDelete();
            $table->unique(['holiday_id', 'school_class_id']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('holiday_class');

        Schema::table('holidays', function (Blueprint $table) {
            $table->dropColumn(['type', 'applies_to']);
            $table->unique('date');
        });
    }
};
