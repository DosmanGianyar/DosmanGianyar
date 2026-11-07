<?php

namespace Tests\Feature;

use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleProfileUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_guru_can_edit_all_profile_details_including_phone_and_subjects(): void
    {
        $guru = User::factory()->create([
            'role'  => 'guru',
            'name'  => 'I Nyoman Sudarma',
            'email' => 'sudarma@sims.sch.id',
        ]);

        $math = Subject::create(['name' => 'Matematika', 'code' => 'MTK']);
        $phys = Subject::create(['name' => 'Fisika', 'code' => 'FIS']);

        $response = $this->actingAs($guru)->put(route('guru.profile.update'), [
            'name'                       => 'I Nyoman Sudarma, S.Pd., M.Pd.',
            'nickname'                   => 'Pak Sudarma',
            'email'                      => 'sudarma.baru@sims.sch.id',
            'phone'                      => '081234567890',
            'nip'                        => '198001012005011001',
            'nik'                        => '5104010101800001',
            'nuptk'                      => '1234567890123456',
            'teacher_code'               => 'P. SUDARMA',
            'gender'                     => 'L',
            'birth_place'                => 'Gianyar',
            'birth_date'                 => '1980-01-01',
            'religion'                   => 'Hindu',
            'citizenship'                => 'WNI',
            'blood_type'                 => 'O',
            'marital_status'             => 'Menikah',
            'employment_status'          => 'PNS',
            'rank_grade'                 => 'Pembina (IV/a)',
            'position'                   => 'Guru Mata Pelajaran',
            'last_education'             => 'S2 (Magister)',
            'alma_mater'                 => 'Undiksha',
            'tmt_date'                   => '2005-01-01',
            'subjects_section_present'   => '1',
            'subject_ids'                => [$math->id, $phys->id],
            'subject'                    => '',
            'address'                    => 'Jl. Astina Utara, Gianyar',
            'kelurahan'                  => 'Gianyar',
            'kecamatan'                  => 'Gianyar',
            'kabupaten'                  => 'Gianyar',
            'emergency_contact_name'     => 'Ni Luh Sari',
            'emergency_contact_phone'    => '081298765432',
            'emergency_contact_relation' => 'Istri',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $guru->refresh();
        $this->assertSame('I Nyoman Sudarma, S.Pd., M.Pd.', $guru->name);
        $this->assertSame('081234567890', $guru->phone);
        $this->assertSame('198001012005011001', $guru->nip);
        $this->assertSame('P. SUDARMA', $guru->teacher_code);
        $this->assertSame('Pembina (IV/a)', $guru->rank_grade);
        $this->assertEqualsCanonicalizing([$math->id, $phys->id], $guru->subjects()->pluck('subjects.id')->all());
        $this->assertSame('Fisika, Matematika', $guru->subject);
    }

    public function test_pegawai_can_edit_all_profile_details_including_work_unit_and_position(): void
    {
        $pegawai = User::factory()->create([
            'role'  => 'pegawai',
            'name'  => 'I Made Staf',
            'email' => 'pegawai@sims.sch.id',
        ]);

        $response = $this->actingAs($pegawai)->put(route('guru.profile.update'), [
            'name'              => 'I Made Staf, S.E.',
            'email'             => 'pegawai@sims.sch.id',
            'phone'             => '081333444555',
            'nip'               => '198502022010011002',
            'nik'               => '5104020202850002',
            'employment_status' => 'PPPK',
            'rank_grade'        => 'Gol. IX',
            'position'          => 'Kepala Tata Usaha',
            'work_unit'         => 'Tata Usaha & Administrasi',
            'last_education'    => 'S1 / D4',
            'alma_mater'        => 'Universitas Udayana',
            'address'           => 'Gianyar',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $pegawai->refresh();
        $this->assertSame('I Made Staf, S.E.', $pegawai->name);
        $this->assertSame('081333444555', $pegawai->phone);
        $this->assertSame('Kepala Tata Usaha', $pegawai->position);
        $this->assertSame('Tata Usaha & Administrasi', $pegawai->work_unit);
    }

    public function test_orangtua_can_edit_profile_details_and_syncs_with_children(): void
    {
        $ortu = User::factory()->create([
            'role'  => 'orangtua',
            'name'  => 'I Ketut Bapak',
            'phone' => '081111222333',
            'email' => '081111222333@ortu.sims.sch.id',
        ]);

        $siswa = User::factory()->create([
            'role'         => 'siswa',
            'name'         => 'Putu Siswa',
            'parent_name'  => 'I Ketut Bapak',
            'parent_phone' => '081111222333',
        ]);

        $ortu->children()->syncWithoutDetaching([$siswa->id]);

        $response = $this->actingAs($ortu)->put(route('orangtua.profile.update'), [
            'name'            => 'I Ketut Bapak Baru, S.H.',
            'nickname'        => 'Pak Ketut',
            'parent_relation' => 'Ayah Kandung',
            'nik'             => '5104030303750003',
            'phone'           => '081999888777',
            'email'           => 'ketut.ortu@gmail.com',
            'gender'          => 'L',
            'birth_place'     => 'Gianyar',
            'birth_date'      => '1975-03-03',
            'religion'        => 'Hindu',
            'citizenship'     => 'WNI',
            'last_education'  => 'S1 / D4',
            'occupation'      => 'Wiraswasta',
            'company_name'    => 'CV Maju Jaya',
            'income_range'    => 'Rp 5.000.000 - Rp 10.000.000',
            'address'         => 'Jl. Dharma Giri, Gianyar',
            'kelurahan'       => 'Bitera',
            'kecamatan'       => 'Gianyar',
            'kabupaten'       => 'Gianyar',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $ortu->refresh();
        $siswa->refresh();

        $this->assertSame('I Ketut Bapak Baru, S.H.', $ortu->name);
        $this->assertSame('081999888777', $ortu->phone);
        $this->assertSame('Ayah Kandung', $ortu->parent_relation);
        $this->assertSame('Wiraswasta', $ortu->occupation);

        $this->assertSame('I Ketut Bapak Baru, S.H.', $siswa->parent_name);
        $this->assertSame('081999888777', $siswa->parent_phone);
        $this->assertSame('I Ketut Bapak Baru, S.H.', $siswa->father_name);
        $this->assertSame('Wiraswasta', $siswa->father_job);
    }
}
