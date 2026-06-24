<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // attendances — queried heavily by user+date and class+date
        Schema::table('attendances', function (Blueprint $table) {
            $table->index(['user_id', 'date'], 'idx_attendances_user_date');
            $table->index('date', 'idx_attendances_date');
            $table->index('status', 'idx_attendances_status');
        });

        // conduct_logs — queried by student and by teacher
        Schema::table('conduct_logs', function (Blueprint $table) {
            $table->index('student_id', 'idx_conduct_logs_student');
            $table->index('teacher_id', 'idx_conduct_logs_teacher');
            $table->index('created_at', 'idx_conduct_logs_created');
        });

        // permits — queried by student and by status
        Schema::table('permits', function (Blueprint $table) {
            $table->index('student_id', 'idx_permits_student');
            $table->index('status', 'idx_permits_status');
            $table->index(['start_date', 'end_date'], 'idx_permits_dates');
        });

        // asset_loans — queried by user, asset, and status
        Schema::table('asset_loans', function (Blueprint $table) {
            $table->index('user_id', 'idx_loans_user');
            $table->index('asset_id', 'idx_loans_asset');
            $table->index('status', 'idx_loans_status');
        });

        // damage_reports — queried by status
        Schema::table('damage_reports', function (Blueprint $table) {
            $table->index('status', 'idx_damage_status');
            $table->index('asset_id', 'idx_damage_asset');
        });

        // voting sessions & votes
        Schema::table('voting_sessions', function (Blueprint $table) {
            $table->index('status', 'idx_voting_status');
        });

        Schema::table('votes', function (Blueprint $table) {
            $table->index('voter_id', 'idx_votes_voter');
        });

        // users — role is used in almost every middleware check
        Schema::table('users', function (Blueprint $table) {
            $table->index('role', 'idx_users_role');
            $table->index('class_id', 'idx_users_class');
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropIndex('idx_attendances_user_date');
            $table->dropIndex('idx_attendances_date');
            $table->dropIndex('idx_attendances_status');
        });
        Schema::table('conduct_logs', function (Blueprint $table) {
            $table->dropIndex('idx_conduct_logs_student');
            $table->dropIndex('idx_conduct_logs_teacher');
            $table->dropIndex('idx_conduct_logs_created');
        });
        Schema::table('permits', function (Blueprint $table) {
            $table->dropIndex('idx_permits_student');
            $table->dropIndex('idx_permits_status');
            $table->dropIndex('idx_permits_dates');
        });
        Schema::table('asset_loans', function (Blueprint $table) {
            $table->dropIndex('idx_loans_user');
            $table->dropIndex('idx_loans_asset');
            $table->dropIndex('idx_loans_status');
        });
        Schema::table('damage_reports', function (Blueprint $table) {
            $table->dropIndex('idx_damage_status');
            $table->dropIndex('idx_damage_asset');
        });
        Schema::table('voting_sessions', function (Blueprint $table) {
            $table->dropIndex('idx_voting_status');
        });
        Schema::table('votes', function (Blueprint $table) {
            $table->dropIndex('idx_votes_voter');
        });
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('idx_users_role');
            $table->dropIndex('idx_users_class');
        });
    }
};
