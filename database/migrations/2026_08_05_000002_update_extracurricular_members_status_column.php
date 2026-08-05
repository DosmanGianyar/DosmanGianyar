<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Change status column from enum to string to support 'rejected' and 'inactive'
        try {
            DB::statement("ALTER TABLE extracurricular_members MODIFY status VARCHAR(30) NOT NULL DEFAULT 'pending_join'");
        } catch (\Throwable $e) {
            // Fallback for sqlite / testing
            Schema::table('extracurricular_members', function (Blueprint $table) {
                $table->string('status', 30)->default('pending_join')->change();
            });
        }
    }

    public function down(): void
    {
        try {
            DB::statement("ALTER TABLE extracurricular_members MODIFY status ENUM('pending_join', 'active', 'pending_leave') NOT NULL DEFAULT 'pending_join'");
        } catch (\Throwable $e) {
            // Silence down fallback
        }
    }
};
