<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\AssetLoan;
use App\Models\DamageReport;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PrasaranaController extends Controller
{
    public function index(): View
    {
        /** @var \App\Models\User $siswa */
        $siswa = Auth::user();
        $siswa->load('schoolClass');

        $stats = [
            'active_loans'   => AssetLoan::where('user_id', $siswa->id)
                ->whereIn('status', ['pending', 'approved', 'active'])
                ->count(),
            'returned_loans' => AssetLoan::where('user_id', $siswa->id)
                ->where('status', 'returned')
                ->count(),
            'damage_pending' => DamageReport::where('reporter_id', $siswa->id)
                ->whereIn('status', ['pending', 'in_progress'])
                ->count(),
            'damage_total'   => DamageReport::where('reporter_id', $siswa->id)->count(),
        ];

        $activeLoans = AssetLoan::with('asset')
            ->where('user_id', $siswa->id)
            ->whereIn('status', ['pending', 'approved', 'active'])
            ->latest()
            ->limit(5)
            ->get();

        $recentDamage = DamageReport::with('asset')
            ->where('reporter_id', $siswa->id)
            ->latest()
            ->limit(5)
            ->get();

        return view('siswa.prasarana.index', compact('siswa', 'stats', 'activeLoans', 'recentDamage'));
    }
}
