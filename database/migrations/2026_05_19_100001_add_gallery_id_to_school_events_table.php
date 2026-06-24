<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('school_events', function (Blueprint $table) {
            $table->foreignId('gallery_id')
                ->nullable()
                ->after('created_by')
                ->constrained('galleries')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('school_events', function (Blueprint $table) {
            $table->dropForeignIdFor(\App\Models\Gallery::class);
        });
    }
};
