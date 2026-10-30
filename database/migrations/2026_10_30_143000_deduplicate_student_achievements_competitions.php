<?php

use App\Services\AchievementPembinaMatcher;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('student_achievements')) {
            AchievementPembinaMatcher::deduplicateCompetitions();
            AchievementPembinaMatcher::syncAllAchievements();
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
