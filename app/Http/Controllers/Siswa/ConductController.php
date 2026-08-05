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

        $allLogs = $user->conductLogs()->with('category')->get();
        $prestasiCount    = $allLogs->filter(fn ($l) => $l->isPrestasi())->count();
        $pelanggaranCount = $allLogs->filter(fn ($l) => $l->isPelanggaran())->count();

        return view('siswa.conduct.index', compact('logs', 'prestasiCount', 'pelanggaranCount'));
    }
}
