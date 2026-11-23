<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('extracurricular_sessions', function (Blueprint $table) {
            if (! Schema::hasColumn('extracurricular_sessions', 'material')) {
                $table->text('material')->nullable()->after('location');
            }
            if (! Schema::hasColumn('extracurricular_sessions', 'activity')) {
                $table->text('activity')->nullable()->after('material');
            }
            if (! Schema::hasColumn('extracurricular_sessions', 'obstacles')) {
                $table->text('obstacles')->nullable()->after('activity');
            }
            if (! Schema::hasColumn('extracurricular_sessions', 'photo')) {
                $table->string('photo')->nullable()->after('notes');
            }
            if (! Schema::hasColumn('extracurricular_sessions', 'updated_by')) {
                $table->unsignedBigInteger('updated_by')->nullable()->after('created_by');
            }
        });

        try {
            if (DB::getDriverName() === 'mysql') {
                DB::statement("ALTER TABLE extracurricular_attendances MODIFY status VARCHAR(20) NOT NULL DEFAULT 'hadir'");
            } else {
                Schema::table('extracurricular_attendances', function (Blueprint $table) {
                    $table->string('status', 20)->default('hadir')->change();
                });
            }
        } catch (\Throwable $e) {
            Schema::table('extracurricular_attendances', function (Blueprint $table) {
                $table->string('status', 20)->default('hadir')->change();
            });
        }

        Schema::table('extracurricular_attendances', function (Blueprint $table) {
            if (! Schema::hasColumn('extracurricular_attendances', 'note')) {
                $table->string('note', 255)->nullable()->after('status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('extracurricular_sessions', function (Blueprint $table) {
            foreach (['material', 'activity', 'obstacles', 'photo', 'updated_by'] as $col) {
                if (Schema::hasColumn('extracurricular_sessions', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        Schema::table('extracurricular_attendances', function (Blueprint $table) {
            if (Schema::hasColumn('extracurricular_attendances', 'note')) {
                $table->dropColumn('note');
            }
        });
    }
};
