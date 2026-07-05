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
        Schema::table('conduct_logs', function (Blueprint $table) {
            $table->text('description')->nullable()->after('note');
            $table->enum('severity', ['ringan', 'sedang', 'berat'])->nullable()->after('description');
            $table->foreignId('category_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('conduct_logs', function (Blueprint $table) {
            $table->dropColumn(['description', 'severity']);
            $table->foreignId('category_id')->nullable(false)->change();
        });
    }
};
