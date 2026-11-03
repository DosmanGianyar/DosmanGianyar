<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\TeacherCodeSyncService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeacherCodeSyncTest extends TestCase
{
    use RefreshDatabase;

    public function test_teacher_code_sync_matches_user_and_sets_code(): void
    {
        $teacher = User::create([
            'name'     => 'I Nyoman Sudarma, S.Ag',
            'email'    => 'test_sudarma_' . uniqid() . '@sims.sch.id',
            'password' => bcrypt('password'),
            'role'     => 'guru',
        ]);

        $result = TeacherCodeSyncService::syncAll();

        $this->assertGreaterThanOrEqual(1, $result['matched']);
        $this->assertEquals('P. SUDARMA', $teacher->fresh()->teacher_code);

        $teacher->forceDelete();
    }
}
