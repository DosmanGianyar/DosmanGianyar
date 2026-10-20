<?php

namespace Tests\Feature;

use App\Filament\Resources\OrangtuaResource\Pages\ListOrangtuas;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class OrangtuaResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_access_orangtua_resource_page(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get('/admin/orangtuas');
        $response->assertStatus(200);

        Livewire::actingAs($admin)
            ->test(ListOrangtuas::class)
            ->assertSuccessful();
    }

    public function test_can_search_and_filter_orangtua_by_student_name(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $class = SchoolClass::create([
            'name'  => 'X MIPA 1',
            'grade' => 'X',
        ]);

        // Student 1 & Parent 1
        $student1 = User::factory()->create([
            'name'     => 'I Kadek Raditya',
            'role'     => 'siswa',
            'class_id' => $class->id,
            'nis'      => '12345',
        ]);
        $parent1 = User::factory()->create([
            'name'  => 'Wayan Ortu Raditya',
            'role'  => 'orangtua',
            'phone' => '081200000001',
        ]);
        $parent1->children()->attach($student1->id);

        // Student 2 & Parent 2
        $student2 = User::factory()->create([
            'name'     => 'Ni Made Anindya',
            'role'     => 'siswa',
            'class_id' => $class->id,
            'nis'      => '67890',
        ]);
        $parent2 = User::factory()->create([
            'name'  => 'Ketut Ortu Anindya',
            'role'  => 'orangtua',
            'phone' => '081200000002',
        ]);
        $parent2->children()->attach($student2->id);

        // Test 1: Global table search by student name
        Livewire::actingAs($admin)
            ->test(ListOrangtuas::class)
            ->searchTable('Raditya')
            ->assertCanSeeTableRecords([$parent1])
            ->assertCanNotSeeTableRecords([$parent2]);

        // Test 2: Filter student_search by student name
        Livewire::actingAs($admin)
            ->test(ListOrangtuas::class)
            ->filterTable('student_search', [
                'student_name' => 'Anindya',
            ])
            ->assertCanSeeTableRecords([$parent2])
            ->assertCanNotSeeTableRecords([$parent1]);

        // Test 3: Filter by class_id
        Livewire::actingAs($admin)
            ->test(ListOrangtuas::class)
            ->filterTable('class_id', $class->id)
            ->assertCanSeeTableRecords([$parent1, $parent2]);
    }
}
