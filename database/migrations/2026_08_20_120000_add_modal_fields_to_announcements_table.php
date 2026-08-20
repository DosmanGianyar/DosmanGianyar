<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            $table->string('image')->nullable()->after('body');
            $table->boolean('is_active')->default(true)->after('is_pinned');
            $table->boolean('show_as_modal')->default(true)->after('is_active');
            $table->timestamp('expires_at')->nullable()->after('published_at');
        });
    }

    public function down(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            $table->dropColumn(['image', 'is_active', 'show_as_modal', 'expires_at']);
        });
    }
};
