<?php

namespace Tests\Feature;

use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BukuIndukPrintTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_print_single_buku_induk(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $class = SchoolClass::create(['name' => 'X MIPA 1', 'grade' => 'X']);
        $siswa = User::factory()->create([
            'role'              => 'siswa',
            'class_id'          => $class->id,
            'nisn'              => '0071234568',
            'nis'               => '2026008',
            'nickname'          => 'Ilham',
            'birth_place'       => 'Pengadang',
            'religion'          => 'Islam',
            'citizenship'       => 'WNI',
            'child_order'       => 1,
            'siblings_count'    => 2,
            'orphan_status'     => 'Lengkap',
            'daily_language'    => 'Bahasa Sasak dan Indonesia',
            'living_with'       => 'Tinggal dengan orang tua',
            'physical_disability' => 'DB',
            'father_name'       => 'Nasrullah',
            'mother_name'       => 'Nasripah',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.buku-induk.print', $siswa->id));

        $response->assertStatus(200);
        $response->assertSee('II. DATA PRIBADI SISWA');
        $response->assertSee('A. KETERANGAN TENTANG DIRI SISWA');
        $response->assertSee('B. KETERANGAN TEMPAT TINGGAL');
        $response->assertSee('C. KETERANGAN KESEHATAN');
        $response->assertSee('D. KETERANGAN PENDIDIKAN');
        $response->assertSee('E. KETERANGAN TENTANG AYAH KANDUNG');
        $response->assertSee('F. KETERANGAN TENTANG IBU KANDUNG');
        $response->assertSee('G. KETERANGAN TENTANG WALI');
        $response->assertSee($siswa->name);
        $response->assertSee('Ilham');
        $response->assertSee('Nasrullah');
        $response->assertSee('Nasripah');
    }

    public function test_admin_can_print_buku_induk_per_class(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $class = SchoolClass::create(['name' => 'X MIPA 2', 'grade' => 'X']);

        $s1 = User::factory()->create(['role' => 'siswa', 'class_id' => $class->id, 'name' => 'Siswa A']);
        $s2 = User::factory()->create(['role' => 'siswa', 'class_id' => $class->id, 'name' => 'Siswa B']);

        $response = $this->actingAs($admin)->get(route('admin.buku-induk.print-class', $class->id));

        $response->assertStatus(200);
        $response->assertSee('Siswa A');
        $response->assertSee('Siswa B');
    }
}
