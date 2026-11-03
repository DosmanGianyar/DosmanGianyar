<?php

namespace Tests\Feature;

use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\User;
use App\Services\ScheduleImportService;
use App\Services\ScheduleMatrixExport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ScheduleMatrixImportExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_parse_and_export_matrix_schedule(): void
    {
        // 1. Setup sample classes and teachers
        $class = SchoolClass::create(['name' => 'X-01', 'grade' => 10]);
        $subject = Subject::create(['name' => 'SOSIOLOGI', 'code' => 'SOS']);
        $teacher = User::create([
            'name'         => 'Putu Ayu Dewi Puspita Wulandari, S.Sos.',
            'email'        => 'puspita@sims.sch.id',
            'password'     => bcrypt('password'),
            'role'         => 'guru',
            'teacher_code' => 'B. Puspita',
        ]);
        $teacher->subjects()->attach($subject->id);

        $filePath = base_path('public/templates/jadwal 5okt.xlsx');
        $this->assertFileExists($filePath);

        $service = new ScheduleImportService();
        $items = $service->parseCsvSchedule($filePath, 'ALL');

        $this->assertNotEmpty($items);
        $this->assertGreaterThan(1000, count($items));

        // Find items for B. Puspita in X-01
        $puspitaItems = array_filter($items, fn($i) => $i['class_name'] === 'X-01' && $i['teacher_code'] === 'B. Puspita');
        $this->assertNotEmpty($puspitaItems);

        $firstPuspita = reset($puspitaItems);
        $this->assertEquals($teacher->id, $firstPuspita['teacher_id']);
        $this->assertEquals($subject->id, $firstPuspita['subject_id']);
        $this->assertEquals(1, $firstPuspita['day']); // Senin

        // 2. Test saveSchedules
        $count = $service->saveSchedules($items, '2026/2027 Ganjil', true);
        $this->assertGreaterThan(0, $count);
        $this->assertDatabaseHas('schedules', [
            'class_id'   => $class->id,
            'teacher_id' => $teacher->id,
            'subject_id' => $subject->id,
            'day'        => 1,
            'period'     => 2,
        ]);

        // 3. Test ScheduleMatrixExport
        $exporter = new ScheduleMatrixExport();
        $spreadsheet = $exporter->generateSpreadsheet('2026/2027 Ganjil');
        $this->assertNotNull($spreadsheet);

        $sheet = $spreadsheet->getActiveSheet();
        $this->assertEquals('JADWAL GURU', $sheet->getTitle());
        $this->assertEquals('JADWAL GURU', $sheet->getCell('B3')->getValue());
        $this->assertEquals('X-01', $sheet->getCell('B7')->getValue());
    }
}
