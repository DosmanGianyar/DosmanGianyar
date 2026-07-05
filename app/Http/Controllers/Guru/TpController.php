<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\TujuanPembelajaran;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class TpController extends Controller
{
    public function index(): View
    {
        $teacher      = Auth::user();
        $mySubjectIds = $teacher->subjects()->pluck('subjects.id')->toArray();

        $tps = TujuanPembelajaran::where(function ($q) use ($teacher, $mySubjectIds) {
            $q->where('teacher_id', $teacher->id);
            if (count($mySubjectIds)) {
                $q->orWhere(function ($q2) use ($teacher, $mySubjectIds) {
                    $q2->whereIn('subject_id', $mySubjectIds)
                       ->where('teacher_id', '!=', $teacher->id);
                });
            }
        })
        ->with(['subject', 'teacher'])
        ->orderBy('subject_id')
        ->orderByDesc('id')
        ->get();

        $subjects = $teacher->subjects()->orderBy('name')->get();

        return view('guru.tp.index', compact('tps', 'subjects'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'subject_id'  => 'nullable|exists:subjects,id',
            'code'        => 'nullable|string|max:30',
            'description' => 'required|string|max:500',
        ]);

        TujuanPembelajaran::create([
            'teacher_id'  => Auth::id(),
            'subject_id'  => $request->subject_id ?: null,
            'code'        => $request->code ?: null,
            'description' => $request->description,
            'is_active'   => true,
        ]);

        return back()->with('success', 'Tujuan Pembelajaran berhasil ditambahkan.');
    }

    public function update(Request $request, TujuanPembelajaran $tp): RedirectResponse
    {
        abort_unless($tp->teacher_id === Auth::id(), 403, 'Akses ditolak.');

        $request->validate([
            'subject_id'  => 'nullable|exists:subjects,id',
            'code'        => 'nullable|string|max:30',
            'description' => 'required|string|max:500',
        ]);

        $tp->update([
            'subject_id'  => $request->subject_id ?: null,
            'code'        => $request->code ?: null,
            'description' => $request->description,
        ]);

        return back()->with('success', 'Tujuan Pembelajaran berhasil diperbarui.');
    }

    public function toggle(TujuanPembelajaran $tp): RedirectResponse
    {
        abort_unless($tp->teacher_id === Auth::id(), 403, 'Akses ditolak.');
        $tp->update(['is_active' => !$tp->is_active]);
        return back()->with('success', $tp->fresh()->is_active ? 'TP diaktifkan.' : 'TP dinonaktifkan.');
    }

    public function destroy(TujuanPembelajaran $tp): RedirectResponse
    {
        abort_unless($tp->teacher_id === Auth::id(), 403, 'Akses ditolak.');
        $tp->delete();
        return back()->with('success', 'Tujuan Pembelajaran berhasil dihapus.');
    }
}
