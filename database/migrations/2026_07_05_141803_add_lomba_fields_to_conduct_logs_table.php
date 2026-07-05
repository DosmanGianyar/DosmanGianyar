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
            $table->enum('prestasi_type', ['perilaku', 'lomba'])->nullable()->after('severity');
            $table->string('lomba_name', 200)->nullable()->after('prestasi_type');
            $table->enum('lomba_level', ['sekolah', 'kabupaten', 'provinsi', 'nasional', 'internasional'])->nullable()->after('lomba_name');
            $table->enum('lomba_rank', ['juara_1', 'juara_2', 'juara_3', 'harapan', 'peserta'])->nullable()->after('lomba_level');
        });
    }

    public function down(): void
    {
        Schema::table('conduct_logs', function (Blueprint $table) {
            $table->dropColumn(['prestasi_type', 'lomba_name', 'lomba_level', 'lomba_rank']);
        });
    }
};
