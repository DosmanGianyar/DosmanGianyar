<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'nik')) {
                $table->string('nik', 30)->nullable()->after('nip');
            }
            if (! Schema::hasColumn('users', 'nuptk')) {
                $table->string('nuptk', 30)->nullable()->after('nik');
            }
            if (! Schema::hasColumn('users', 'employment_status')) {
                $table->string('employment_status', 50)->nullable()->after('nuptk');
            }
            if (! Schema::hasColumn('users', 'rank_grade')) {
                $table->string('rank_grade', 50)->nullable()->after('employment_status');
            }
            if (! Schema::hasColumn('users', 'position')) {
                $table->string('position', 100)->nullable()->after('rank_grade');
            }
            if (! Schema::hasColumn('users', 'work_unit')) {
                $table->string('work_unit', 100)->nullable()->after('position');
            }
            if (! Schema::hasColumn('users', 'last_education')) {
                $table->string('last_education', 50)->nullable()->after('work_unit');
            }
            if (! Schema::hasColumn('users', 'alma_mater')) {
                $table->string('alma_mater', 150)->nullable()->after('last_education');
            }
            if (! Schema::hasColumn('users', 'tmt_date')) {
                $table->date('tmt_date')->nullable()->after('alma_mater');
            }
            if (! Schema::hasColumn('users', 'marital_status')) {
                $table->string('marital_status', 30)->nullable()->after('tmt_date');
            }
            if (! Schema::hasColumn('users', 'parent_relation')) {
                $table->string('parent_relation', 50)->nullable()->after('marital_status');
            }
            if (! Schema::hasColumn('users', 'occupation')) {
                $table->string('occupation', 100)->nullable()->after('parent_relation');
            }
            if (! Schema::hasColumn('users', 'company_name')) {
                $table->string('company_name', 150)->nullable()->after('occupation');
            }
            if (! Schema::hasColumn('users', 'income_range')) {
                $table->string('income_range', 100)->nullable()->after('company_name');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $columns = [
                'nik', 'nuptk', 'employment_status', 'rank_grade', 'position',
                'work_unit', 'last_education', 'alma_mater', 'tmt_date',
                'marital_status', 'parent_relation', 'occupation', 'company_name', 'income_range',
            ];
            foreach ($columns as $col) {
                if (Schema::hasColumn('users', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
