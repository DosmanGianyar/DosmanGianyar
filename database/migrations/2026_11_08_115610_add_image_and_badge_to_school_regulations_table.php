<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('school_regulations', function (Blueprint $table) {
            $table->string('image')->nullable()->after('content');
            $table->json('images')->nullable()->after('image');
            $table->string('badge_text', 50)->nullable()->after('title');
        });
    }

    public function down(): void
    {
        Schema::table('school_regulations', function (Blueprint $table) {
            $table->dropColumn(['image', 'images', 'badge_text']);
        });
    }
};
