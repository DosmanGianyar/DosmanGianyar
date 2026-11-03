<?php

namespace Tests\Feature;

use App\Exports\TeacherCodeExport;
use App\Models\User;
use App\Services\TeacherCodeImportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

class TeacherCodeImportExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_teacher_code_export_collection_and_mapping(): void
    {
        $teacher = User::create([
            'name'         => 'I Nyoman Sudarma, S.Ag',
            'email'        => 'sudarma@sims.sch.id',
            'nip'          => '197607232005011001',
            'role'         => 'guru',
            'password'     => bcrypt('secret'),
            'teacher_code' => 'P. SUDARMA',
        ]);

        $export = new TeacherCodeExport();
        $collection = $export->collection();
        $this->assertTrue($collection->contains('id', $teacher->id));

        $mapped = $export->map($teacher);
        $this->assertEquals('P. SUDARMA', $mapped[1]);
        $this->assertEquals('I Nyoman Sudarma, S.Ag', $mapped[2]);
        $this->assertEquals('197607232005011001', $mapped[3]);
    }

    public function test_teacher_code_import_update_and_replace_modes(): void
    {
        $teacherA = User::create([
            'name'         => 'Guru A Test',
            'email'        => 'gurua@sims.sch.id',
            'nip'          => '11111111',
            'role'         => 'guru',
            'password'     => bcrypt('secret'),
            'teacher_code' => 'P. GURU A',
        ]);

        $teacherB = User::create([
            'name'         => 'Guru B Test',
            'email'        => 'gurub@sims.sch.id',
            'nip'          => '22222222',
            'role'         => 'guru',
            'password'     => bcrypt('secret'),
            'teacher_code' => 'B. GURU B',
        ]);

        // Create temporary CSV file
        $csvPath = tempnam(sys_get_temp_dir(), 'tcode_') . '.csv';
        $csvContent = "no,kode_guru,nama_guru,nip,mata_pelajaran,email\n" .
                      "1,P. GURU A BARU,Guru A Test,11111111,Matematika,gurua@sims.sch.id\n";
        file_put_contents($csvPath, $csvContent);

        $service = new TeacherCodeImportService();

        // 1. Test UPDATE mode: Teacher A updated, Teacher B preserved
        $resUpdate = $service->import($csvPath, 'update');
        $this->assertTrue($resUpdate['success']);
        $this->assertEquals(1, $resUpdate['updated']);
        $this->assertEquals('P. GURU A BARU', $teacherA->fresh()->teacher_code);
        $this->assertEquals('B. GURU B', $teacherB->fresh()->teacher_code);

        // 2. Test REPLACE mode: Teacher A kept as new code, Teacher B unlisted so cleared
        $resReplace = $service->import($csvPath, 'replace');
        $this->assertTrue($resReplace['success']);
        $this->assertEquals('P. GURU A BARU', $teacherA->fresh()->teacher_code);
        $this->assertNull($teacherB->fresh()->teacher_code);
        $this->assertGreaterThanOrEqual(1, $resReplace['cleared']);

        @unlink($csvPath);
    }
}
