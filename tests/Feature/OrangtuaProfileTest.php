<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class OrangtuaProfileTest extends TestCase
{
    use RefreshDatabase;

    private function makeFamily(string $phone = '081234567890'): array
    {
        $orangtua = User::factory()->create([
            'name'     => 'Orangtua Siswa Satu',
            'role'     => 'orangtua',
            'phone'    => $phone,
            'password' => Hash::make($phone),
        ]);

        $student = User::factory()->create([
            'role'        => 'siswa',
            'parent_name' => $orangtua->name,
            'parent_phone'=> $phone,
        ]);

        $orangtua->children()->attach($student->id);

        return [$orangtua, $student];
    }

    public function test_orangtua_can_view_profile_page_on_web(): void
    {
        [$orangtua, $student] = $this->makeFamily();

        $response = $this->actingAs($orangtua)->get(route('orangtua.profile'));

        $response->assertOk();
        $response->assertSee($orangtua->name);
        $response->assertSee($orangtua->phone);
        $response->assertSee($student->name);
    }

    public function test_orangtua_can_edit_own_name_on_web(): void
    {
        [$orangtua, $student] = $this->makeFamily();

        $response = $this->actingAs($orangtua)->put(route('orangtua.profile.update'), [
            'name'    => 'I Wayan Nama Baru',
            'address' => 'Jl. Kebo Iwa No. 12, Gianyar',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $orangtua->refresh();
        $this->assertEquals('I Wayan Nama Baru', $orangtua->name);
        $this->assertEquals('Jl. Kebo Iwa No. 12, Gianyar', $orangtua->address);

        // Data parent_name pada anak ikut tersinkronisasi
        $student->refresh();
        $this->assertEquals('I Wayan Nama Baru', $student->parent_name);
    }

    public function test_orangtua_cannot_change_phone_number_on_web(): void
    {
        [$orangtua] = $this->makeFamily('081234567890');

        $response = $this->actingAs($orangtua)->put(route('orangtua.profile.update'), [
            'name'  => 'I Made Updated',
            'phone' => '089999999999', // mencoba mengganti nomor HP
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $orangtua->refresh();
        $this->assertEquals('I Made Updated', $orangtua->name);
        // Nomor HP tetap nomor awal, tidak berubah sama sekali
        $this->assertEquals('081234567890', $orangtua->phone);
    }

    public function test_orangtua_cannot_set_empty_name_on_web(): void
    {
        [$orangtua] = $this->makeFamily();

        $response = $this->actingAs($orangtua)->put(route('orangtua.profile.update'), [
            'name' => '',
        ]);

        $response->assertSessionHasErrors(['name']);
        $this->assertEquals('Orangtua Siswa Satu', $orangtua->fresh()->name);
    }

    public function test_orangtua_can_update_name_via_api(): void
    {
        [$orangtua, $student] = $this->makeFamily();

        $response = $this->actingAs($orangtua, 'sanctum')->putJson('/api/v1/auth/profile', [
            'name'    => 'Nama Ortu Dari API',
            'address' => 'Jl. Astina Gianyar',
        ]);

        $response->assertOk();
        $response->assertJsonPath('user.name', 'Nama Ortu Dari API');

        $orangtua->refresh();
        $this->assertEquals('Nama Ortu Dari API', $orangtua->name);
        $this->assertEquals('Jl. Astina Gianyar', $orangtua->address);

        $student->refresh();
        $this->assertEquals('Nama Ortu Dari API', $student->parent_name);
    }

    public function test_orangtua_cannot_change_phone_number_via_api(): void
    {
        [$orangtua] = $this->makeFamily('081234567890');

        $response = $this->actingAs($orangtua, 'sanctum')->putJson('/api/v1/auth/profile', [
            'name'  => 'Nama Ortu Baru',
            'phone' => '087777777777', // mencoba mengganti nomor HP via API
        ]);

        $response->assertOk();
        $response->assertJsonPath('user.name', 'Nama Ortu Baru');
        // Nomor HP yang dikembalikan API tetap nomor lama
        $response->assertJsonPath('user.phone', '081234567890');

        $orangtua->refresh();
        $this->assertEquals('Nama Ortu Baru', $orangtua->name);
        $this->assertEquals('081234567890', $orangtua->phone);
    }

    public function test_orangtua_cannot_set_empty_name_via_api(): void
    {
        [$orangtua] = $this->makeFamily();

        $response = $this->actingAs($orangtua, 'sanctum')->putJson('/api/v1/auth/profile', [
            'name' => '   ',
        ]);

        $response->assertStatus(422);
        $this->assertEquals('Orangtua Siswa Satu', $orangtua->fresh()->name);
    }

    public function test_orangtua_can_change_password_on_web(): void
    {
        [$orangtua] = $this->makeFamily('081234567890');

        $response = $this->actingAs($orangtua)->put(route('orangtua.profile.password'), [
            'current_password'      => '081234567890',
            'password'              => 'PasswordBaru123',
            'password_confirmation' => 'PasswordBaru123',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertTrue(Hash::check('PasswordBaru123', $orangtua->fresh()->password));
    }

    public function test_siswa_cannot_access_orangtua_profile(): void
    {
        $siswa = User::factory()->create(['role' => 'siswa']);

        $response = $this->actingAs($siswa)->get(route('orangtua.profile'));

        $response->assertForbidden();
    }
}
