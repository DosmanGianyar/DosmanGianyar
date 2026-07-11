<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Perluas dulu supaya value lama & baru sama-sama valid selama masa transisi data —
        // MySQL menolak/mengosongkan (truncate) value yang belum terdaftar di definisi ENUM,
        // jadi backfill tidak bisa dijalankan sebelum ENUM memuat value tujuannya.
        DB::statement("ALTER TABLE assets MODIFY category ENUM('furniture', 'elektronik', 'olahraga', 'lab', 'perpustakaan', 'lain', 'perpus', 'sarana', 'prasarana') NOT NULL");

        DB::table('assets')->where('category', 'perpustakaan')->update(['category' => 'perpus']);
        DB::table('assets')->whereIn('category', ['furniture', 'elektronik', 'olahraga', 'lab', 'lain'])
            ->update(['category' => 'sarana']);

        // Persempit ke set final setelah semua baris terjamin memakai value baru.
        DB::statement("ALTER TABLE assets MODIFY category ENUM('perpus', 'sarana', 'prasarana') NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE assets MODIFY category ENUM('perpus', 'sarana', 'prasarana', 'furniture', 'elektronik', 'olahraga', 'lab', 'perpustakaan', 'lain') NOT NULL");

        DB::table('assets')->where('category', 'perpus')->update(['category' => 'perpustakaan']);
        DB::table('assets')->where('category', 'sarana')->update(['category' => 'lain']);
        DB::table('assets')->where('category', 'prasarana')->update(['category' => 'lain']);

        DB::statement("ALTER TABLE assets MODIFY category ENUM('furniture', 'elektronik', 'olahraga', 'lab', 'perpustakaan', 'lain') NOT NULL");
    }
};
