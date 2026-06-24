<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ConductController extends Controller
{
    public function index(): View
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $logs = $user->conductLogs()
            ->with('category')
            ->latest()
            ->paginate(20);

        $totalPoint  = $user->conductLogs()->sum('point');
        $prestasi    = $user->conductLogs()->where('point', '>', 0)->sum('point');
        $pelanggaran = $user->conductLogs()->where('point', '<', 0)->sum('point');

        // Monthly net point trend — last 6 months
        $trend = collect();
        for ($i = 5; $i >= 0; $i--) {
            $m   = now()->subMonths($i);
            $net = $user->conductLogs()
                ->whereYear('created_at', $m->year)
                ->whereMonth('created_at', $m->month)
                ->sum('point');
            $trend->push(['label' => $m->isoFormat('MMM'), 'net' => (int) $net]);
        }

        return view('siswa.conduct.index', compact('logs', 'totalPoint', 'prestasi', 'pelanggaran', 'trend'));
    }
}
