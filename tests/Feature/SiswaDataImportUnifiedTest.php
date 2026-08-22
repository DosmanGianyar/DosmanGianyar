<?php

namespace Tests\Feature;

use App\Imports\SiswaDataImport;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Tests\TestCase;

class SiswaDataImportUnifiedTest extends TestCase
{
    use RefreshDatabase;

    public function test_nisn_is_primary_key_and_nis_can_be_updated(): void
    {
        $class = SchoolClass::create(['name' => 'X MIPA 1', 'grade' => 'X']);

        // Create student with old (wrong) NIS
        $student = User::create([
            'name'                 => 'I Kadek Student',
            'email'                => '0071234568@siswa.sims.sch.id',
            'password'             => bcrypt('password'),
            'role'                 => 'siswa',
            'nisn'                 => '0071234568',
            'nis'                  => 'OLD_WRONG_NIS',
            'class_id'             => $class->id,
            'must_change_password' => false,
        ]);

        // Another student currently holding the new NIS that needs to be reassigned
        $otherStudent = User::create([
            'name'                 => 'Other Student',
            'email'                => '0079999999@siswa.sims.sch.id',
            'password'             => bcrypt('password'),
            'role'                 => 'siswa',
            'nisn'                 => '0079999999',
            'nis'                  => '2026008',
            'class_id'             => $class->id,
            'must_change_password' => false,
        ]);

        // Simulasikan baris excel import dengan NISN yang sama tapi NIS baru 2026008
        $import = new SiswaDataImport();
        $colMap = [
            'nisn' => 0,
            'nis' => 1,
            'nama' => 2,
            'email' => 3,
            'kelas' => 4,
        ];

        $row = collect([
            '0071234568', // NISN (Unchanged Key)
            '2026008',    // NIS (New Correct NIS)
            'I Kadek Student Updated', // Nama baru
            '0071234568@siswa.sims.sch.id',
            'X MIPA 1',
        ]);

        $import->processRow($row, $colMap, 2);

        $this->assertEquals(1, $import->updated);
        $this->assertDatabaseHas('users', [
            'id'   => $student->id,
            'nisn' => '0071234568',
            'nis'  => '2026008',
            'name' => 'I Kadek Student Updated',
        ]);

        // Verified that conflicting user's NIS was cleared so update succeeded without error
        $this->assertNull($otherStudent->fresh()->nis);
    }
}
