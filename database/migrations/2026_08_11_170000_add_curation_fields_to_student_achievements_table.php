<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('student_achievements', function (Blueprint $table) {
            $table->string('event_name')->nullable()->after('title');
            $table->string('organizer')->nullable()->after('event_name');
            $table->string('field_category')->default('akademik')->after('category_id');
            $table->string('participation_type')->default('individu')->after('rank');
            $table->text('event_url')->nullable()->after('certificate');
            $table->string('assignment_letter')->nullable()->after('event_url');
            $table->string('curation_status')->default('pending')->after('status');
            $table->text('curation_note')->nullable()->after('curation_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_achievements', function (Blueprint $table) {
            $table->dropColumn([
                'event_name',
                'organizer',
                'field_category',
                'participation_type',
                'event_url',
                'assignment_letter',
                'curation_status',
                'curation_note',
            ]);
        });
    }
};
