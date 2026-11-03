<?php

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
        Schema::table('damage_reports', function (Blueprint $table) {
            if (! Schema::hasColumn('damage_reports', 'title')) {
                $table->string('title')->nullable()->after('reporter_id');
            }
            if (! Schema::hasColumn('damage_reports', 'room_id')) {
                $table->foreignId('room_id')->nullable()->after('asset_id')->constrained('rooms')->nullOnDelete();
            }
            $table->foreignId('asset_id')->nullable()->change();
            $table->string('photo')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('damage_reports', function (Blueprint $table) {
            if (Schema::hasColumn('damage_reports', 'room_id')) {
                $table->dropForeign(['room_id']);
                $table->dropColumn('room_id');
            }
            if (Schema::hasColumn('damage_reports', 'title')) {
                $table->dropColumn('title');
            }
            $table->foreignId('asset_id')->nullable(false)->change();
            $table->string('photo')->nullable(false)->change();
        });
    }
};
