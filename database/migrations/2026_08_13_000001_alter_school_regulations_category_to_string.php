<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Modify category column to VARCHAR(50) to support all regulation categories
        if (Schema::hasTable('school_regulations')) {
            if (DB::getDriverName() !== 'sqlite') {
                DB::statement("ALTER TABLE school_regulations MODIFY category VARCHAR(50) NOT NULL");
            }
        }
    }

    public function down(): void
    {
        // No-op for rollback
    }
};
