<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\EarlyCheckoutRequest;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class EarlyCheckoutRequestController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->input('status', 'pending');
        $query  = EarlyCheckoutRequest::with(['student.schoolClass'])
            ->orderByDesc('date')
            ->orderBy('requested_time');

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $requests    = $query->paginate(15)->withQueryString();
        $pendingCount = EarlyCheckoutRequest::where('status', 'pending')->count();

        return view('guru.early-checkout.index', compact('requests', 'status', 'pendingCount'));
    }

    public function approve(Request $request, EarlyCheckoutRequest $earlyCheckout): RedirectResponse
    {
        $this->authorizePending($earlyCheckout);

        $data = $request->validate([
            'reviewer_note' => 'nullable|string|max:255',
        ]);

        $earlyCheckout->update([
            'status'        => 'approved',
            'reviewed_by'   => Auth::id(),
            'reviewed_at'   => now(),
            'reviewer_note' => $data['reviewer_note'] ?? null,
        ]);

        NotificationService::send(
            userId: $earlyCheckout->student_id,
            title:  'Izin Pulang Awal Disetujui',
            body:   'Pengajuan pulang lebih awal tanggal ' . $earlyCheckout->date->isoFormat('D MMMM Y') . ' pukul ' . $earlyCheckout->requestedTimeFormatted() . ' telah disetujui.',
            type:   'success',
            url:    route('siswa.early-checkout.index'),
        );

        return back()->with('success', 'Pengajuan disetujui. Siswa dapat melakukan absen pulang lebih awal.');
    }

    public function reject(Request $request, EarlyCheckoutRequest $earlyCheckout): RedirectResponse
    {
        $this->authorizePending($earlyCheckout);

        $data = $request->validate([
            'reviewer_note' => 'required|string|max:255',
        ]);

        $earlyCheckout->update([
            'status'        => 'rejected',
            'reviewed_by'   => Auth::id(),
            'reviewed_at'   => now(),
            'reviewer_note' => $data['reviewer_note'],
        ]);

        NotificationService::send(
            userId: $earlyCheckout->student_id,
            title:  'Izin Pulang Awal Ditolak',
            body:   'Pengajuan pulang lebih awal tanggal ' . $earlyCheckout->date->isoFormat('D MMMM Y') . ' ditolak. Alasan: ' . $data['reviewer_note'],
            type:   'warning',
            url:    route('siswa.early-checkout.index'),
        );

        return back()->with('success', 'Pengajuan ditolak.');
    }

    private function authorizePending(EarlyCheckoutRequest $earlyCheckout): void
    {
        if (! $earlyCheckout->isPending()) {
            abort(403, 'Pengajuan ini sudah diproses.');
        }
    }
}
