<?php

namespace Tests\Feature;

use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SecurityHardeningTest extends TestCase
{
    use RefreshDatabase;

    public function test_exposed_student_excel_and_csv_files_are_removed_from_public(): void
    {
        $this->assertFileDoesNotExist(public_path('data-siswa.xlsx'));
        $this->assertFileDoesNotExist(public_path('ekstra.csv'));
        $this->assertFileDoesNotExist(public_path('file/DATA PER 4 FEBRUARI.xlsx'));
    }

    public function test_students_are_forbidden_from_unprotected_admin_web_routes(): void
    {
        $class = SchoolClass::create(['name' => 'X-1', 'grade' => 'X']);
        $student1 = User::factory()->create([
            'role'     => 'siswa',
            'class_id' => $class->id,
            'nis'      => '1001',
            'nisn'     => '0080001001',
        ]);
        $student2 = User::factory()->create([
            'role'     => 'siswa',
            'class_id' => $class->id,
            'nis'      => '1002',
            'nisn'     => '0080001002',
        ]);
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($student1)->get(route('admin.buku-induk.print', $student2))->assertForbidden();
        $this->actingAs($student1)->get(route('admin.buku-induk.print-class', $class))->assertForbidden();
        $this->actingAs($student1)->post(route('admin.students.delete-photo', $student2))->assertForbidden();
        $this->actingAs($student1)->get(route('admin.library.clearance-card', $student2))->assertForbidden();
        $this->actingAs($student1)->get(route('admin.library.visit-qr-card'))->assertForbidden();
        $this->actingAs($student1)->get(route('admin.library.monthly-loan-report'))->assertForbidden();
        $this->actingAs($student1)->get(route('admin.library.visit-report'))->assertForbidden();

        $this->actingAs($admin)->get(route('admin.buku-induk.print', $student2))->assertOk();
    }

    public function test_biodata_prevents_student_idor_and_allows_self_or_staff(): void
    {
        $class = SchoolClass::create(['name' => 'XI-1', 'grade' => 'XI']);
        $student1 = User::factory()->create([
            'role'     => 'siswa',
            'class_id' => $class->id,
            'nis'      => '2001',
            'nisn'     => '0080002001',
        ]);
        $student2 = User::factory()->create([
            'role'     => 'siswa',
            'class_id' => $class->id,
            'nis'      => '2002',
            'nisn'     => '0080002002',
        ]);
        $guru = User::factory()->create(['role' => 'guru']);
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($student1)->get(route('public.biodata', $student2->id))->assertForbidden();
        $this->actingAs($student1)->get(route('public.biodata', $student1->id))->assertOk();

        $this->actingAs($guru)->get(route('public.biodata', $student2->id))->assertOk();
        $this->actingAs($admin)->get(route('public.biodata', $student2->id))->assertOk();
    }

    public function test_student_card_verification_rejects_numeric_id_enumeration(): void
    {
        $student = User::factory()->create([
            'role'          => 'siswa',
            'name'          => 'Siswa Terdaftar Rahasia',
            'nis'           => '3001',
            'nisn'          => '0090003001',
            'qr_code_token' => 'TOKEN-ABC-3001',
        ]);

        // Numerik ID murni tidak boleh membuka data siswa
        $resById = $this->get(route('student-card.verify', (string) $student->id));
        $resById->assertOk();
        $resById->assertDontSee('Siswa Terdaftar Rahasia');

        // Pencocokan sah via qr_code_token / nisn tetap berhasil
        $resByToken = $this->get(route('student-card.verify', 'TOKEN-ABC-3001'));
        $resByToken->assertOk();
        $resByToken->assertSee('SISWA TERDAFTAR RAHASIA');
    }

    public function test_students_cannot_access_guru_api_endpoints_while_teachers_can(): void
    {
        $student = User::factory()->create(['role' => 'siswa']);
        $guru = User::factory()->create(['role' => 'guru']);

        $headers = [
            'Accept'      => 'application/json',
            'X-Device-ID' => 'test-device-security',
        ];

        Sanctum::actingAs($student);
        $this->withHeaders($headers)->getJson('/api/v1/guru/layanan')->assertForbidden();
        $this->withHeaders($headers)->getJson('/api/v1/guru/conduct-students')->assertForbidden();
        $this->withHeaders($headers)->getJson('/api/v1/guru/bk-consultations')->assertForbidden();

        Sanctum::actingAs($guru);
        $this->withHeaders($headers)->getJson('/api/v1/guru/layanan')->assertOk();
        $this->withHeaders($headers)->getJson('/api/v1/guru/conduct-students')->assertOk();
    }
}
