<?php

namespace Tests\Feature;

use App\Filament\Widgets\AttendanceChartWidget;
use App\Filament\Widgets\ConductChartWidget;
use App\Filament\Widgets\ExecutiveOverviewWidget;
use App\Filament\Widgets\ExtracurricularChartWidget;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ExecutiveDashboardAnalyticsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_render_executive_overview_widget(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin);

        Livewire::test(ExecutiveOverviewWidget::class)
            ->assertSee('Tingkat Kehadiran Hari Ini')
            ->assertSee('Total Siswa & Pengelola');
    }

    public function test_admin_can_render_analytics_chart_widgets(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin);

        Livewire::test(AttendanceChartWidget::class)->assertStatus(200);
        Livewire::test(ConductChartWidget::class)->assertStatus(200);
        Livewire::test(ExtracurricularChartWidget::class)->assertStatus(200);
    }
}
