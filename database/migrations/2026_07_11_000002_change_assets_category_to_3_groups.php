<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            DB::statement('PRAGMA ignore_check_constraints = ON');
        } else {
            DB::statement("ALTER TABLE assets MODIFY category ENUM('furniture', 'elektronik', 'olahraga', 'lab', 'perpustakaan', 'lain', 'perpus', 'sarana', 'prasarana') NOT NULL");
        }

        DB::table('assets')->where('category', 'perpustakaan')->update(['category' => 'perpus']);
        DB::table('assets')->whereIn('category', ['furniture', 'elektronik', 'olahraga', 'lab', 'lain'])
            ->update(['category' => 'sarana']);

        if (DB::getDriverName() === 'sqlite') {
            DB::statement('PRAGMA ignore_check_constraints = OFF');
        } else {
            DB::statement("ALTER TABLE assets MODIFY category ENUM('perpus', 'sarana', 'prasarana') NOT NULL");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE assets MODIFY category ENUM('perpus', 'sarana', 'prasarana', 'furniture', 'elektronik', 'olahraga', 'lab', 'perpustakaan', 'lain') NOT NULL");
        }

        DB::table('assets')->where('category', 'perpus')->update(['category' => 'perpustakaan']);
        DB::table('assets')->where('category', 'sarana')->update(['category' => 'lain']);
        DB::table('assets')->where('category', 'prasarana')->update(['category' => 'lain']);

        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE assets MODIFY category ENUM('furniture', 'elektronik', 'olahraga', 'lab', 'perpustakaan', 'lain') NOT NULL");
        }
    }
};
