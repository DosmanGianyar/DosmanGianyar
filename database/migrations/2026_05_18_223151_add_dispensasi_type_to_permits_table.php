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
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE permits MODIFY COLUMN type ENUM('izin','sakit','dispensasi') NOT NULL");
        }
        // SQLite stores enums as plain strings — no schema change needed
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE permits MODIFY COLUMN type ENUM('izin','sakit') NOT NULL");
        }
    }
};
