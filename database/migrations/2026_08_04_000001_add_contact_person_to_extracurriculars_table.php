<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('extracurriculars') && ! Schema::hasColumn('extracurriculars', 'contact_person')) {
            Schema::table('extracurriculars', function (Blueprint $table) {
                $table->string('contact_person')->nullable()->after('name');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('extracurriculars') && Schema::hasColumn('extracurriculars', 'contact_person')) {
            Schema::table('extracurriculars', function (Blueprint $table) {
                $table->dropColumn('contact_person');
            });
        }
    }
};
