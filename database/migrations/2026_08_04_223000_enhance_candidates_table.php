<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('candidates', function (Blueprint $table) {
            if (!Schema::hasColumn('candidates', 'candidate_number')) {
                $table->integer('candidate_number')->default(1)->after('voting_session_id');
            }
            if (!Schema::hasColumn('candidates', 'vice_name')) {
                $table->string('vice_name')->nullable()->after('name');
            }
            if (!Schema::hasColumn('candidates', 'mission')) {
                $table->text('mission')->nullable()->after('vision');
            }
            if (!Schema::hasColumn('candidates', 'programs')) {
                $table->text('programs')->nullable()->after('mission');
            }
            if (!Schema::hasColumn('candidates', 'motto')) {
                $table->string('motto')->nullable()->after('programs');
            }
            if (!Schema::hasColumn('candidates', 'video_url')) {
                $table->string('video_url')->nullable()->after('motto');
            }
        });
    }

    public function down(): void
    {
        Schema::table('candidates', function (Blueprint $table) {
            $table->dropColumn(['candidate_number', 'vice_name', 'mission', 'programs', 'motto', 'video_url']);
        });
    }
};
