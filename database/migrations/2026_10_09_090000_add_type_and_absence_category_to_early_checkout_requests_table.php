<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('early_checkout_requests', function (Blueprint $table) {
            if (! Schema::hasColumn('early_checkout_requests', 'type')) {
                $table->string('type', 30)->default('dengan_absen')->after('reason');
            }
            if (! Schema::hasColumn('early_checkout_requests', 'absence_category')) {
                $table->string('absence_category', 30)->nullable()->after('type');
            }
            if (! Schema::hasColumn('early_checkout_requests', 'checked_out_at')) {
                $table->timestamp('checked_out_at')->nullable()->after('reviewer_note');
            }
        });
    }

    public function down(): void
    {
        Schema::table('early_checkout_requests', function (Blueprint $table) {
            $table->dropColumn(['type', 'absence_category', 'checked_out_at']);
        });
    }
};
