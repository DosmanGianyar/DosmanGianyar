<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('student_achievements', 'achievement_code')) {
            Schema::table('student_achievements', function (Blueprint $table) {
                $table->string('achievement_code', 30)->nullable()->unique()->index()->after('id');
            });
        }

        // Backfill achievement_code for all existing records
        $existing = DB::table('student_achievements')
            ->whereNull('achievement_code')
            ->orderBy('id')
            ->get(['id', 'achievement_date', 'created_at']);

        $usedCodes = [];

        foreach ($existing as $item) {
            $year = date('Y');
            if ($item->achievement_date) {
                $parsed = strtotime($item->achievement_date);
                if ($parsed) {
                    $year = date('Y', $parsed);
                }
            } elseif ($item->created_at) {
                $parsed = strtotime($item->created_at);
                if ($parsed) {
                    $year = date('Y', $parsed);
                }
            }

            $chars = '23456789ABCDEFGHJKMNPQRSTUVWXYZ';
            do {
                $rand = '';
                for ($i = 0; $i < 4; $i++) {
                    $rand .= $chars[random_int(0, strlen($chars) - 1)];
                }
                $code = "PRS-{$year}-{$rand}";
            } while (in_array($code, $usedCodes) || DB::table('student_achievements')->where('achievement_code', $code)->exists());

            $usedCodes[] = $code;

            DB::table('student_achievements')
                ->where('id', $item->id)
                ->update(['achievement_code' => $code]);
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('student_achievements', 'achievement_code')) {
            Schema::table('student_achievements', function (Blueprint $table) {
                $table->dropColumn('achievement_code');
            });
        }
    }
};
