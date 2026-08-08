<?php

namespace Tests\Feature;

use App\Models\Extracurricular;
use App\Models\ExtracurricularMember;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExtracurricularGradeFilterPdfTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_filter_members_pdf_by_grade_and_class(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $pembina = User::factory()->create(['role' => 'guru', 'name' => 'Pembina Test']);

        $classX  = SchoolClass::create(['name' => 'X-1', 'grade' => 'X']);
        $classXI = SchoolClass::create(['name' => 'XI-1', 'grade' => 'XI']);

        $siswaX  = User::factory()->create(['role' => 'siswa', 'class_id' => $classX->id]);
        $siswaXI = User::factory()->create(['role' => 'siswa', 'class_id' => $classXI->id]);

        $ekstra = Extracurricular::create([
            'name'        => 'Paskibra',
            'pembina_id'  => $pembina->id,
            'max_members' => 50,
        ]);
        $ekstra->teachers()->attach($pembina->id);

        ExtracurricularMember::create(['extracurricular_id' => $ekstra->id, 'user_id' => $siswaX->id, 'status' => 'active', 'role' => 'ketua']);
        ExtracurricularMember::create(['extracurricular_id' => $ekstra->id, 'user_id' => $siswaXI->id, 'status' => 'active', 'role' => 'member']);

        // Test PDF download without filter
        $responseAll = $this->actingAs($admin)->get(route('admin.extracurricular.members.pdf', $ekstra->id));
        $responseAll->assertStatus(200);
        $responseAll->assertHeader('content-type', 'application/pdf');

        // Test PDF download with grade X filter
        $responseGradeX = $this->actingAs($admin)->get(route('admin.extracurricular.members.pdf', [$ekstra->id, 'grade' => 'X']));
        $responseGradeX->assertStatus(200);
        $responseGradeX->assertHeader('content-type', 'application/pdf');

        // Test PDF download with class_id filter
        $responseClass = $this->actingAs($admin)->get(route('admin.extracurricular.members.pdf', [$ekstra->id, 'class_id' => $classX->id]));
        $responseClass->assertStatus(200);
        $responseClass->assertHeader('content-type', 'application/pdf');
    }

    public function test_can_download_no_ekstra_pdf_with_grade_filter(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $classXI = SchoolClass::create(['name' => 'XI-2', 'grade' => 'XI']);
        User::factory()->create(['role' => 'siswa', 'class_id' => $classXI->id]);

        $response = $this->actingAs($admin)->get(route('admin.extracurricular.no-ekstra.pdf', ['grade' => 'XI']));
        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }
}
