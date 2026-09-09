<?php

namespace Tests\Feature;

use App\Imports\GuruImport;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class GuruPegawaiImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_creates_new_pegawai_account_when_nip_not_registered(): void
    {
        $importer = new GuruImport();
        $colMap = $importer->buildColMap(['nip', 'nama lengkap', 'role', 'email', 'no hp', 'jenis kelamin']);

        $row = collect([
            '198805122014021003',
            'I Made Pegawai, S.E.',
            'Pegawai',
            'made.pegawai@pegawai.sims.sch.id',
            '081234500013',
            'L',
        ]);

        $importer->processRow($row, $colMap, 2);

        $this->assertEquals(1, $importer->created);
        $this->assertEquals(0, $importer->updated);

        $user = User::where('nip', '198805122014021003')->first();
        $this->assertNotNull($user);
        $this->assertEquals('I Made Pegawai, S.E.', $user->name);
        $this->assertEquals('pegawai', $user->role);
        $this->assertTrue($user->isPegawai());
        $this->assertTrue(Hash::check('198805122014021003', $user->password));
    }

    public function test_updates_existing_user_name_with_degrees_and_details_when_nip_exists(): void
    {
        // Existing user before update (without gelar)
        $existing = User::factory()->create([
            'name'     => 'I Wayan Guru',
            'nip'      => '198001012006041001',
            'role'     => 'guru',
            'phone'    => '08111111111',
            'gender'   => 'L',
        ]);

        $importer = new GuruImport();
        $colMap = $importer->buildColMap(['nip', 'nama lengkap', 'role', 'no hp', 'mata pelajaran']);

        // New row has academic title/gelar
        $row = collect([
            '198001012006041001',
            'Drs. I Wayan Guru, M.Pd.',
            'Guru',
            '081234500011',
            'Matematika',
        ]);

        $importer->processRow($row, $colMap, 2);

        $this->assertEquals(0, $importer->created);
        $this->assertEquals(1, $importer->updated);

        $existing->refresh();
        $this->assertEquals('Drs. I Wayan Guru, M.Pd.', $existing->name);
        $this->assertEquals('guru', $existing->role);
        $this->assertEquals('081234500011', $existing->phone);
        $this->assertTrue($existing->subjects()->where('name', 'Matematika')->exists());
    }

    public function test_uses_default_role_when_role_column_not_in_file(): void
    {
        $importer = new GuruImport();
        $importer->defaultRole = 'pegawai';

        $colMap = $importer->buildColMap(['nip', 'nama lengkap']);
        $row = collect(['199208202019032004', 'Ni Kadek Staff TU, S.Kom.']);

        $importer->processRow($row, $colMap, 2);

        $this->assertEquals(1, $importer->created);

        $user = User::where('nip', '199208202019032004')->first();
        $this->assertNotNull($user);
        $this->assertEquals('pegawai', $user->role);
        $this->assertEquals('Ni Kadek Staff TU, S.Kom.', $user->name);
    }

    public function test_can_import_from_generated_template_csv(): void
    {
        $templateCsv = public_path('templates/contoh-import-guru-pegawai.csv');
        $this->assertFileExists($templateCsv);

        $importer = new GuruImport();
        \Maatwebsite\Excel\Facades\Excel::import($importer, $templateCsv);

        $this->assertEquals(4, $importer->created);

        // Check Guru 1
        $guru1 = User::where('nip', '198001012006041001')->first();
        $this->assertNotNull($guru1);
        $this->assertEquals('Drs. I Wayan Guru, M.Pd.', $guru1->name);
        $this->assertEquals('guru', $guru1->role);
        $this->assertTrue(Hash::check('198001012006041001', $guru1->password));

        // Check Pegawai 1
        $pegawai1 = User::where('nip', '198805122014021003')->first();
        $this->assertNotNull($pegawai1);
        $this->assertEquals('I Made Pegawai, S.E.', $pegawai1->name);
        $this->assertEquals('pegawai', $pegawai1->role);
        $this->assertTrue($pegawai1->isPegawai());
        $this->assertTrue(Hash::check('198805122014021003', $pegawai1->password));

        // Check Pegawai 2
        $pegawai2 = User::where('nip', '199208202019032004')->first();
        $this->assertNotNull($pegawai2);
        $this->assertEquals('Ni Kadek Staff TU, S.Kom.', $pegawai2->name);
        $this->assertEquals('pegawai', $pegawai2->role);
        $this->assertTrue($pegawai2->isPegawai());
    }
}
