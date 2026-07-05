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
        Schema::table('conduct_logs', function (Blueprint $table) {
            $table->enum('type', ['pelanggaran', 'prestasi'])->nullable()->after('teacher_id');
        });

        // Backfill: record dengan severity → pelanggaran, lainnya → prestasi
        \DB::statement("UPDATE conduct_logs SET type = 'pelanggaran' WHERE severity IS NOT NULL");
        \DB::statement("UPDATE conduct_logs SET type = 'prestasi' WHERE severity IS NULL AND (category_id IS NOT NULL OR prestasi_type IS NOT NULL)");
    }

    public function down(): void
    {
        Schema::table('conduct_logs', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
};
