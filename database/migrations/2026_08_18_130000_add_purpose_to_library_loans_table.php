<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('library_loans') && ! Schema::hasColumn('library_loans', 'purpose')) {
            Schema::table('library_loans', function (Blueprint $table) {
                $table->string('purpose')->nullable()->after('notes');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('library_loans') && Schema::hasColumn('library_loans', 'purpose')) {
            Schema::table('library_loans', function (Blueprint $table) {
                $table->dropColumn('purpose');
            });
        }
    }
};
