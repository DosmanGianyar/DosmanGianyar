<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('student_achievements', 'team_code')) {
            Schema::table('student_achievements', function (Blueprint $table) {
                $table->string('team_code', 50)->nullable()->index()->after('participation_type');
            });
        }

        // Backfill team_code for existing beregu achievements
        $bereguItems = DB::table('student_achievements')
            ->where('participation_type', 'beregu')
            ->whereNull('team_code')
            ->get();

        $groups = $bereguItems->groupBy(function ($item) {
            return strtolower(trim($item->title)) . '|' . $item->achievement_date;
        });

        foreach ($groups as $groupKey => $items) {
            $teamCode = 'TEAM-' . strtoupper(Str::random(8)) . '-' . time();
            $ids = $items->pluck('id')->toArray();
            DB::table('student_achievements')
                ->whereIn('id', $ids)
                ->update(['team_code' => $teamCode]);
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('student_achievements', 'team_code')) {
            Schema::table('student_achievements', function (Blueprint $table) {
                $table->dropColumn('team_code');
            });
        }
    }
};
