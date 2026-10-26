<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('conduct_logs', function (Blueprint $table) {
            $table->text('rejection_note')->nullable()->after('verifier_id');
        });
    }

    public function down(): void
    {
        Schema::table('conduct_logs', function (Blueprint $table) {
            $table->dropColumn(['rejection_note']);
        });
    }
};
