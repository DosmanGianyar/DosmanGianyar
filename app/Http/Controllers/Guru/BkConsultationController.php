<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\BkConsultation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BkConsultationController extends Controller
{
    public function index(Request $request): View
    {
        $teacher = auth()->user();
        abort_unless($teacher->isBk(), 403);

        $status = $request->input('status', '');

        $consultations = BkConsultation::where('teacher_id', $teacher->id)
            ->when($status, fn($q) => $q->where('status', $status))
            ->with(['student:id,name,nis,class_id', 'student.schoolClass:id,name'])
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $counts = BkConsultation::where('teacher_id', $teacher->id)
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('guru.bk.consultations', compact('consultations', 'counts', 'status'));
    }

    public function schedule(Request $request, BkConsultation $consultation): RedirectResponse
    {
        abort_unless($consultation->teacher_id === auth()->id() && $consultation->isPending(), 403);

        $request->validate(['scheduled_date' => 'required|date|after_or_equal:today']);

        $consultation->update(['status' => 'scheduled', 'scheduled_date' => $request->scheduled_date]);

        return back()->with('success', 'Bimbingan berhasil dijadwalkan.');
    }

    public function complete(Request $request, BkConsultation $consultation): RedirectResponse
    {
        abort_unless(
            $consultation->teacher_id === auth()->id() &&
            in_array($consultation->status, ['pending', 'scheduled']),
            403
        );

        $request->validate([
            'conducted_date' => 'required|date',
            'teacher_note'   => 'required|string|max:2000',
            'follow_up'      => 'nullable|string|max:1000',
        ]);

        $consultation->update([
            'status'         => 'completed',
            'conducted_date' => $request->conducted_date,
            'teacher_note'   => $request->teacher_note,
            'follow_up'      => $request->follow_up,
        ]);

        return back()->with('success', 'Jurnal bimbingan BK berhasil disimpan.');
    }

    public function cancel(Request $request, BkConsultation $consultation): RedirectResponse
    {
        abort_unless(
            $consultation->teacher_id === auth()->id() &&
            in_array($consultation->status, ['pending', 'scheduled']),
            403
        );

        $request->validate(['cancelled_reason' => 'nullable|string|max:300']);

        $consultation->update([
            'status'           => 'cancelled',
            'cancelled_reason' => $request->cancelled_reason ?: 'Dibatalkan oleh Guru BK',
        ]);

        return back()->with('success', 'Pengajuan bimbingan dibatalkan.');
    }
}
