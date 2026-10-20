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
        Schema::table('chat_rooms', function (Blueprint $table) {
            $table->string('type', 30)->default('trio')->after('school_class_id'); // 'trio' (bersama) atau 'parent_teacher' (privat ortu-wali)
            $table->unique(['student_id', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('chat_rooms', function (Blueprint $table) {
            $table->dropUnique(['student_id', 'type']);
            $table->dropColumn('type');
        });
    }
};
