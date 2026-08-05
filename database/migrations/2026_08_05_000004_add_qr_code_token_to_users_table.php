<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use App\Models\User;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'qr_code_token')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('qr_code_token', 64)->nullable()->unique()->after('device_locked_at');
            });
        }

        // Generate UUID / random tokens for all existing students
        User::whereNull('qr_code_token')
            ->orWhere('qr_code_token', '')
            ->chunkById(200, function ($users) {
                foreach ($users as $u) {
                    $u->timestamps = false;
                    $u->qr_code_token = (string) Str::uuid();
                    $u->save();
                }
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('users', 'qr_code_token')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('qr_code_token');
            });
        }
    }
};
