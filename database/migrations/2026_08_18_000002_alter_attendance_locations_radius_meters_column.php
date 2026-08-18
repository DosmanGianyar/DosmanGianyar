<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendance_locations', function (Blueprint $table) {
            $table->unsignedInteger('radius_meters')->default(50)->change();
        });
    }

    public function down(): void
    {
        Schema::table('attendance_locations', function (Blueprint $table) {
            $table->unsignedSmallInteger('radius_meters')->default(50)->change();
        });
    }
};
