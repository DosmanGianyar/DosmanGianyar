<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('conduct_logs', function (Blueprint $table) {
            $table->foreignId('teacher_id')->nullable()->change();
            $table->boolean('is_self_reported')->default(false)->after('note');
            $table->string('status', 20)->default('verified')->after('is_self_reported');
            $table->timestamp('verified_at')->nullable()->after('status');
            $table->foreignId('verifier_id')->nullable()->after('verified_at')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('conduct_logs', function (Blueprint $table) {
            $table->dropForeign(['verifier_id']);
            $table->dropColumn(['is_self_reported', 'status', 'verified_at', 'verifier_id']);
        });
    }
};
