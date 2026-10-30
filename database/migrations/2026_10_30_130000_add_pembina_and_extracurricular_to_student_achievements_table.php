<?php

use App\Services\AchievementPembinaMatcher;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('student_achievements')) {
            return;
        }

        Schema::table('student_achievements', function (Blueprint $table) {
            if (! Schema::hasColumn('student_achievements', 'extracurricular_id')) {
                $table->foreignId('extracurricular_id')
                    ->nullable()
                    ->after('category_id')
                    ->constrained('extracurriculars')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('student_achievements', 'pembina_id')) {
                $table->foreignId('pembina_id')
                    ->nullable()
                    ->after('extracurricular_id')
                    ->constrained('users')
                    ->nullOnDelete();
            }
        });

        try {
            AchievementPembinaMatcher::syncAllAchievements();
        } catch (\Throwable $e) {
            // Jangan gagalkan migrasi apabila tabel terkait sedang kosong / dalam proses testing
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('student_achievements')) {
            return;
        }

        Schema::table('student_achievements', function (Blueprint $table) {
            if (Schema::hasColumn('student_achievements', 'pembina_id')) {
                $table->dropConstrainedForeignId('pembina_id');
            }

            if (Schema::hasColumn('student_achievements', 'extracurricular_id')) {
                $table->dropConstrainedForeignId('extracurricular_id');
            }
        });
    }
};

