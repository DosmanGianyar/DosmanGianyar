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

        $prestasiCount    = $user->conductLogs()->whereHas('category', fn ($q) => $q->where('type', 'prestasi'))->count();
        $pelanggaranCount = $user->conductLogs()->whereHas('category', fn ($q) => $q->where('type', 'pelanggaran'))->count();

        return view('siswa.conduct.index', compact('logs', 'prestasiCount', 'pelanggaranCount'));
    }
}
