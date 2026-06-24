<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        /** @var \App\Models\User $guru */
        $guru = Auth::user();
        $guru->load('homeroomClass.students');
        $classId = $guru->homeroomClass?->id;

        $totalStudents = $classId
            ? User::where('role', 'siswa')->where('class_id', $classId)->count()
            : 0;

        // Critical BK alerts: students with total point ≤ -75
        // Use whereRaw subquery for cross-DB compatibility (SQLite does not allow HAVING on subquery alias)
        $recentAlerts = User::where('role', 'siswa')
            ->when($classId, fn($q) => $q->where('class_id', $classId))
            ->whereRaw('(SELECT COALESCE(SUM(point), 0) FROM conduct_logs WHERE student_id = users.id) <= ?', [-75])
            ->withSum('conductLogs', 'point')
            ->orderByRaw('(SELECT COALESCE(SUM(point), 0) FROM conduct_logs WHERE student_id = users.id) ASC')
            ->limit(5)
            ->get()
            ->map(fn($s) => [
                'name'  => $s->name,
                'class' => $s->schoolClass?->name ?? '—',
                'point' => (int) ($s->conduct_logs_sum_point ?? 0),
            ]);

        $stats = [
            'alert_kritis'   => $recentAlerts->count(),
            'total_students' => $totalStudents,
        ];

        return view('guru.dashboard', compact('guru', 'stats', 'recentAlerts'));
    }
}
