<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tujuan_pembelajaran', function (Blueprint $table) {
            if (!Schema::hasColumn('tujuan_pembelajaran', 'grade_level')) {
                $table->string('grade_level', 10)->nullable()->after('subject_id')->comment('Tingkatan kelas: 10, 11, 12');
            }
        });
    }

    public function down(): void
    {
        Schema::table('tujuan_pembelajaran', function (Blueprint $table) {
            if (Schema::hasColumn('tujuan_pembelajaran', 'grade_level')) {
                $table->dropColumn('grade_level');
            }
        });
    }
};
