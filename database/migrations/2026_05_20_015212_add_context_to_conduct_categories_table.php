<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('conduct_categories', function (Blueprint $table) {
            $table->string('context', 20)->nullable()->after('type');
        });

        // Assign context to categories seeded by DummyDataSeeder
        DB::table('conduct_categories')
            ->whereIn('name', ['Terlambat masuk sekolah', 'Seragam tidak lengkap'])
            ->update(['context' => 'sidak']);

        DB::table('conduct_categories')
            ->where('name', 'Mewakili sekolah dalam lomba')
            ->update(['context' => 'lomba']);

        DB::table('conduct_categories')
            ->where('type', 'prestasi')->whereNull('context')
            ->update(['context' => 'akademik']);

        DB::table('conduct_categories')
            ->where('type', 'pelanggaran')->whereNull('context')
            ->update(['context' => 'kelas']);
    }

    public function down(): void
    {
        Schema::table('conduct_categories', function (Blueprint $table) {
            $table->dropColumn('context');
        });
    }
};
