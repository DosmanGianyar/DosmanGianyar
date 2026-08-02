<?php

namespace Tests\Feature;

use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AngkatanTest extends TestCase
{
    use RefreshDatabase;

    public function test_angkatan_mapping(): void
    {
        $class10 = SchoolClass::create(['name' => 'X-01', 'grade' => '10']);
        $class11 = SchoolClass::create(['name' => 'XI-03', 'grade' => '11']);
        $class12 = SchoolClass::create(['name' => 'XII-07', 'grade' => '12']);

        $siswa10 = User::factory()->create(['role' => 'siswa', 'class_id' => $class10->id]);
        $siswa11 = User::factory()->create(['role' => 'siswa', 'class_id' => $class11->id]);
        $siswa12 = User::factory()->create(['role' => 'siswa', 'class_id' => $class12->id]);

        $this->assertEquals('Angkatan 62', $siswa10->angkatan);
        $this->assertEquals('Angkatan 61', $siswa11->angkatan);
        $this->assertEquals('Angkatan 60', $siswa12->angkatan);
    }
}
