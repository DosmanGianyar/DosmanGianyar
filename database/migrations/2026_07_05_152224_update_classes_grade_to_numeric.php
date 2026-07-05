<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Ubah nilai grade dari Roman numeral ke angka
        DB::table('classes')->where('grade', 'X')->update(['grade' => '10']);
        DB::table('classes')->where('grade', 'XI')->update(['grade' => '11']);
        DB::table('classes')->where('grade', 'XII')->update(['grade' => '12']);
    }

    public function down(): void
    {
        DB::table('classes')->where('grade', '10')->update(['grade' => 'X']);
        DB::table('classes')->where('grade', '11')->update(['grade' => 'XI']);
        DB::table('classes')->where('grade', '12')->update(['grade' => 'XII']);
    }
};
