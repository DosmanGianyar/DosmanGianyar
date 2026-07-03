<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('conduct_categories', function (Blueprint $table) {
            $table->dropColumn('point_value');
        });

        Schema::table('conduct_logs', function (Blueprint $table) {
            $table->dropColumn('point');
        });

        Schema::table('bk_logs', function (Blueprint $table) {
            $table->dropColumn('point_at_time');
        });
    }

    public function down(): void
    {
        Schema::table('conduct_categories', function (Blueprint $table) {
            $table->integer('point_value')->default(0)->after('type');
        });

        Schema::table('conduct_logs', function (Blueprint $table) {
            $table->integer('point')->default(0)->after('category_id');
        });

        Schema::table('bk_logs', function (Blueprint $table) {
            $table->integer('point_at_time')->nullable()->after('coaching_note');
        });
    }
};
