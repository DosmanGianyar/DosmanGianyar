<?php

use App\Services\TeacherCodeSyncService;
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
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'teacher_code')) {
                $table->string('teacher_code', 50)->nullable()->unique()->after('nip');
            }
        });

        // Langsung isi kode guru untuk seluruh 64 guru yang terdata
        TeacherCodeSyncService::syncAll();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'teacher_code')) {
                $table->dropColumn('teacher_code');
            }
        });
    }
};
