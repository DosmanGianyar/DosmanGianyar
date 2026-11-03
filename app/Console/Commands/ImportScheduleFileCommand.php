<?php

namespace App\Console\Commands;

use App\Services\ScheduleImportService;
use Illuminate\Console\Command;

class ImportScheduleFileCommand extends Command
{
    protected $signature = 'schedule:import-file {file : Path relative to base_path or absolute} {--year=2026/2027 Ganjil} {--dry-run}';
    protected $description = 'Import matrix or list schedule file into database';

    public function handle(ScheduleImportService $service): int
    {
        $filePath = $this->argument('file');
        if (!file_exists($filePath)) {
            $filePath = base_path($filePath);
        }

        if (!file_exists($filePath)) {
            $this->error("File not found: {$filePath}");
            return 1;
        }

        $this->info("Parsing schedule file: {$filePath}");
        $items = $service->parseCsvSchedule($filePath, 'ALL');

        $this->info("Total slots parsed: " . count($items));

        $matchedTeachers = 0;
        $matchedSubjects = 0;
        foreach ($items as $item) {
            if (!empty($item['teacher_id'])) $matchedTeachers++;
            if (!empty($item['subject_id'])) $matchedSubjects++;
        }

        $this->info("Matched Teachers: {$matchedTeachers} / " . count($items));
        $this->info("Matched Subjects: {$matchedSubjects} / " . count($items));

        if ($this->option('dry-run')) {
            $this->info("[Dry Run] Selesai tanpa menyimpan ke database.");
            return 0;
        }

        $academicYear = $this->option('year');
        $this->info("Saving schedules for academic year: {$academicYear} (replacing existing)...");
        $saved = $service->saveSchedules($items, $academicYear, true);

        $this->info("Successfully saved {$saved} schedule slots into database!");
        return 0;
    }
}
