<?php

namespace Tests\Feature;

use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\TeacherJournal;
use App\Models\TeacherJournalAbsence;
use App\Models\TujuanPembelajaran;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JournalEditTest extends TestCase
{
    use RefreshDatabase;

    public function test_guru_can_access_edit_journal_page(): void
    {
        $teacher = User::factory()->create(['role' => 'guru']);
        $class   = SchoolClass::create(['name' => 'X IPA 1', 'grade' => 10]);

        $journal = TeacherJournal::create([
            'teacher_id' => $teacher->id,
            'class_id'   => $class->id,
            'date'       => now()->toDateString(),
            'material'   => 'Materi Asli',
            'activity'   => 'Aktivitas Asli',
        ]);

        $response = $this->actingAs($teacher)->get(route('guru.journal.edit', $journal));

        $response->assertStatus(200);
        $response->assertSee('Edit Jurnal Mengajar');
        $response->assertSee('Materi Asli');
    }

    public function test_guru_can_update_journal_and_tp_and_absences(): void
    {
        $teacher = User::factory()->create(['role' => 'guru']);
        $class   = SchoolClass::create(['name' => 'X IPA 1', 'grade' => 10]);
        $subject = Subject::create(['name' => 'Matematika']);
        $student = User::factory()->create(['role' => 'siswa', 'class_id' => $class->id]);

        $tpOld = TujuanPembelajaran::create([
            'teacher_id'  => $teacher->id,
            'subject_id'  => $subject->id,
            'code'        => 'TP1',
            'description' => 'Tujuan Lama',
            'is_active'   => true,
        ]);

        $tpNew = TujuanPembelajaran::create([
            'teacher_id'  => $teacher->id,
            'subject_id'  => $subject->id,
            'code'        => 'TP2',
            'description' => 'Tujuan Baru',
            'is_active'   => true,
        ]);

        $journal = TeacherJournal::create([
            'teacher_id'          => $teacher->id,
            'class_id'            => $class->id,
            'subject_id'          => $subject->id,
            'tp_id'               => $tpOld->id,
            'date'                => now()->toDateString(),
            'period'              => 1,
            'learning_objectives' => '[TP1] Tujuan Lama',
            'material'            => 'Materi Lama',
            'activity'            => 'Diskusi Lama',
        ]);

        TeacherJournalAbsence::create([
            'journal_id' => $journal->id,
            'student_id' => $student->id,
            'status'     => 'sakit',
        ]);

        // Perform update: Change TP to $tpNew and change student status to 'izin'
        $response = $this->actingAs($teacher)->put(route('guru.journal.update', $journal), [
            'class_id'   => $class->id,
            'subject_id' => $subject->id,
            'tp_id'      => $tpNew->id,
            'date'       => now()->toDateString(),
            'period'     => 1,
            'period_end' => 2,
            'material'   => 'Materi Terbarui',
            'activity'   => 'Praktikum Terbarui',
            'notes'      => 'Catatan Terbarui',
            'absent_students' => [
                ['student_id' => $student->id, 'status' => 'izin'],
            ],
        ]);

        $response->assertRedirect(route('guru.journal.index'));
        $response->assertSessionHas('success');

        $journal->refresh();
        $this->assertEquals($tpNew->id, $journal->tp_id);
        $this->assertEquals('[TP2] Tujuan Baru', $journal->learning_objectives);
        $this->assertEquals('Materi Terbarui', $journal->material);
        $this->assertEquals('Praktikum Terbarui', $journal->activity);
        $this->assertEquals(2, $journal->period_end);

        // Check updated absences
        $this->assertDatabaseHas('teacher_journal_absences', [
            'journal_id' => $journal->id,
            'student_id' => $student->id,
            'status'     => 'izin',
        ]);
        $this->assertDatabaseMissing('teacher_journal_absences', [
            'journal_id' => $journal->id,
            'student_id' => $student->id,
            'status'     => 'sakit',
        ]);
    }
}
