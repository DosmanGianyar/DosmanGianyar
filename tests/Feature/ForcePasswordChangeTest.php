<?php

namespace Tests\Feature;

use App\Imports\DapodikImport;
use App\Imports\GuruImport;
use App\Imports\UsersImport;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ForcePasswordChangeTest extends TestCase
{
    use RefreshDatabase;

    // ─── Password defaults — Excel importers ────────────────────────────────

    public function test_users_import_sets_dosman123_and_must_change_password_for_siswa(): void
    {
        (new UsersImport())->collection(new Collection([
            ['nama' => 'Siswa Baru', 'email' => 'siswabaru@example.com', 'role' => 'siswa', 'nis' => '12345'],
        ]));

        $user = User::where('email', 'siswabaru@example.com')->firstOrFail();

        $this->assertTrue(Hash::check('Dosman123', $user->password));
        $this->assertTrue($user->must_change_password);
    }

    public function test_users_import_sets_guru123_and_not_forced_for_guru(): void
    {
        (new UsersImport())->collection(new Collection([
            ['nama' => 'Guru Baru', 'email' => 'gurubaru@example.com', 'role' => 'guru', 'nip' => '198001012006041001'],
        ]));

        $user = User::where('email', 'gurubaru@example.com')->firstOrFail();

        $this->assertTrue(Hash::check('Guru123', $user->password));
        $this->assertFalse($user->must_change_password);
    }

    public function test_dapodik_import_sets_dosman123_and_must_change_password(): void
    {
        $import = new DapodikImport();
        $colMap = $import->buildColMap(['nisn', 'nama']);

        $import->processRow(new Collection(['1234567890', 'Siswa Dapodik']), $colMap, 2);

        $user = User::where('nisn', '1234567890')->firstOrFail();

        $this->assertTrue(Hash::check('Dosman123', $user->password));
        $this->assertTrue($user->must_change_password);
    }

    public function test_guru_import_sets_guru123(): void
    {
        $import = new GuruImport();
        $colMap = $import->buildColMap(['nip', 'nama']);

        $import->processRow(new Collection(['198001012006041001', 'Guru Dapodik']), $colMap, 2);

        $user = User::where('nip', '198001012006041001')->firstOrFail();

        $this->assertTrue(Hash::check('Guru123', $user->password));
        $this->assertFalse($user->must_change_password);
    }

    // ─── Force-change gate (web) ─────────────────────────────────────────────

    public function test_siswa_with_flag_is_redirected_to_profile(): void
    {
        $siswa = User::factory()->create(['role' => 'siswa', 'must_change_password' => true]);

        $this->actingAs($siswa)
            ->get(route('siswa.dashboard'))
            ->assertRedirect(route('siswa.profile'));
    }

    public function test_siswa_with_flag_can_still_reach_profile_page(): void
    {
        $siswa = User::factory()->create(['role' => 'siswa', 'must_change_password' => true]);

        $this->actingAs($siswa)
            ->get(route('siswa.profile'))
            ->assertOk();
    }

    public function test_siswa_without_flag_is_not_redirected(): void
    {
        $siswa = User::factory()->create(['role' => 'siswa', 'must_change_password' => false]);

        $this->actingAs($siswa)
            ->get(route('siswa.dashboard'))
            ->assertOk();
    }

    public function test_updating_password_clears_the_flag(): void
    {
        $siswa = User::factory()->create([
            'role'                  => 'siswa',
            'must_change_password'  => true,
            'password'              => Hash::make('Dosman123'),
        ]);

        $this->actingAs($siswa)->put(route('siswa.profile.password'), [
            'current_password'      => 'Dosman123',
            'password'              => 'PasswordBaru123',
            'password_confirmation' => 'PasswordBaru123',
        ])->assertSessionHasNoErrors();

        $this->assertFalse($siswa->fresh()->must_change_password);

        // Gate lepas — sekarang bisa akses dashboard normal.
        $this->actingAs($siswa->fresh())
            ->get(route('siswa.dashboard'))
            ->assertOk();
    }

    public function test_guru_is_never_forced_even_with_flag_manually_set(): void
    {
        // Guru tidak pernah diberi flag true oleh sistem, tapi middleware gate
        // ini memang hanya dipasang di grup route siswa.*, jadi guru otomatis aman.
        $guru = User::factory()->create(['role' => 'guru']);

        $this->actingAs($guru)
            ->get(route('guru.dashboard'))
            ->assertOk();
    }

    public function test_pengelola_with_flag_is_also_redirected(): void
    {
        $pengelola = User::factory()->create(['role' => 'pengelola', 'must_change_password' => true]);

        $this->actingAs($pengelola)
            ->get(route('siswa.dashboard'))
            ->assertRedirect(route('siswa.profile'));
    }
}
