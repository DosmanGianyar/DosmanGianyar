<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('teacher_journal_absences', function (Blueprint $table) {
            $table->string('status', 50)->change();
        });
    }

    public function down(): void
    {
        Schema::table('teacher_journal_absences', function (Blueprint $table) {
            $table->string('status', 50)->change();
        });
    }
};
