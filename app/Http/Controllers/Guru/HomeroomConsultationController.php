<?php

namespace App\Http\Controllers\Guru;

use App\Exports\HomeroomConsultationExport;
use App\Http\Controllers\Controller;
use App\Models\HomeroomConsultation;
use App\Models\StudentHomeroomTeacher;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Spatie\Browsershot\Browsershot;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;

class HomeroomConsultationController extends Controller
{
    public function index(Request $request): View
    {
        /** @var \App\Models\User $guru */
        $guru = auth()->user();

        $studentCount = StudentHomeroomTeacher::where('teacher_id', $guru->id)->count();

        $status = $request->query('status', '');

        $consultations = HomeroomConsultation::where('teacher_id', $guru->id)
            ->when($status, fn($q) => $q->where('status', $status))
            ->with('student')
            ->latest()
            ->get();

        $counts = HomeroomConsultation::where('teacher_id', $guru->id)
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('guru.homeroom-consultation.index', compact('consultations', 'studentCount', 'counts', 'status'));
    }

    public function schedule(Request $request, HomeroomConsultation $consultation): RedirectResponse
    {
        $this->authorizeTeacher($consultation);
        abort_unless($consultation->isPending(), 422);

        $request->validate([
            'scheduled_date' => 'required|date|after_or_equal:today',
        ]);

        $consultation->update([
            'status'         => 'scheduled',
            'scheduled_date' => $request->scheduled_date,
        ]);

        return back()->with('success', 'Bimbingan berhasil dijadwalkan.');
    }

    public function complete(Request $request, HomeroomConsultation $consultation): RedirectResponse
    {
        $this->authorizeTeacher($consultation);
        abort_unless($consultation->isScheduled() || $consultation->isPending(), 422);

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

        return back()->with('success', 'Jurnal bimbingan berhasil disimpan.');
    }

    public function cancel(Request $request, HomeroomConsultation $consultation): RedirectResponse
    {
        $this->authorizeTeacher($consultation);
        abort_unless(in_array($consultation->status, ['pending', 'scheduled']), 422);

        $request->validate(['cancelled_reason' => 'nullable|string|max:300']);

        $consultation->update([
            'status'           => 'cancelled',
            'cancelled_reason' => $request->cancelled_reason ?: 'Dibatalkan oleh Guru Wali',
        ]);

        return back()->with('success', 'Pengajuan bimbingan dibatalkan.');
    }

    // ─── Export ──────────────────────────────────────────────────────────────

    public function exportExcel(Request $request): BinaryFileResponse
    {
        $guru = auth()->user();

        $request->validate(['month' => 'required|date_format:Y-m']);

        $safeName = str_replace(' ', '_', $guru->name);
        $filename = 'jurnal_bimbingan_' . $safeName . '_' . $request->month . '.xlsx';

        return Excel::download(new HomeroomConsultationExport($guru->id, $request->month), $filename);
    }

    public function exportPdf(Request $request): Response
    {
        $guru = auth()->user();

        $request->validate(['month' => 'required|date_format:Y-m']);

        [$year, $mon] = explode('-', $request->month);

        $consultations = HomeroomConsultation::where('teacher_id', $guru->id)
            ->where('status', 'completed')
            ->whereYear('conducted_date', $year)
            ->whereMonth('conducted_date', $mon)
            ->with('student')
            ->orderBy('conducted_date')
            ->get();

        $html = view('exports.homeroom-consultation-pdf', [
            'consultations' => $consultations,
            'teacher'       => $guru,
            'month'         => $request->month,
        ])->render();

        $safeName = str_replace(' ', '_', $guru->name);
        $filename = 'jurnal_bimbingan_' . $safeName . '_' . $request->month . '.pdf';

        $pdf = Browsershot::html($html)
            ->format('A4')
            ->margins(15, 15, 15, 15)
            ->waitUntilNetworkIdle()
            ->pdf();

        return response($pdf, 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    private function authorizeTeacher(HomeroomConsultation $consultation): void
    {
        abort_unless($consultation->teacher_id === auth()->id(), 403);
    }
}
