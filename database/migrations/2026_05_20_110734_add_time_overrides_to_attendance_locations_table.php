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
        Schema::table('attendance_locations', function (Blueprint $table) {
            $table->time('check_in_open')->nullable()->after('notes');
            $table->time('check_in_late')->nullable()->after('check_in_open');
            $table->time('check_in_close')->nullable()->after('check_in_late');
            $table->time('check_out_open')->nullable()->after('check_in_close');
        });
    }

    public function down(): void
    {
        Schema::table('attendance_locations', function (Blueprint $table) {
            $table->dropColumn(['check_in_open', 'check_in_late', 'check_in_close', 'check_out_open']);
        });
    }
};
