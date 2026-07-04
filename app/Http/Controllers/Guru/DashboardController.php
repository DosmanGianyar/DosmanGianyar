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

        // Siswa dengan pelanggaran terbanyak di kelas wali
        $recentAlerts = User::where('role', 'siswa')
            ->when($classId, fn($q) => $q->where('class_id', $classId))
            ->withCount(['conductLogs as pelanggaran_count' => fn($q) => $q->whereHas('category', fn($c) => $c->where('type', 'pelanggaran'))])
            ->having('pelanggaran_count', '>', 0)
            ->orderByDesc('pelanggaran_count')
            ->limit(5)
            ->get()
            ->map(fn($s) => [
                'name'  => $s->name,
                'class' => $s->schoolClass?->name ?? '—',
                'point' => $s->pelanggaran_count,
            ]);

        $stats = [
            'alert_kritis'   => $recentAlerts->count(),
            'total_students' => $totalStudents,
        ];

        return view('guru.dashboard', compact('guru', 'stats', 'recentAlerts'));
    }
}
