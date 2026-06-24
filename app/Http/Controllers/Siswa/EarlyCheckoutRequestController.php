<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\EarlyCheckoutRequest;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class EarlyCheckoutRequestController extends Controller
{
    public function index(): View
    {
        $requests = EarlyCheckoutRequest::where('student_id', Auth::id())
            ->latest()
            ->paginate(10);

        return view('siswa.early-checkout.index', compact('requests'));
    }

    public function create(): View
    {
        return view('siswa.early-checkout.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'date'           => 'required|date|after_or_equal:today|before_or_equal:' . now()->addDays(7)->toDateString(),
            'requested_time' => 'required|date_format:H:i',
            'reason'         => 'required|string|max:500',
        ]);

        $student = Auth::user();

        $existing = EarlyCheckoutRequest::where('student_id', $student->id)
            ->where('date', $data['date'])
            ->whereIn('status', ['pending', 'approved'])
            ->first();

        if ($existing) {
            return back()->withErrors(['date' => 'Sudah ada pengajuan untuk tanggal ini.'])->withInput();
        }

        EarlyCheckoutRequest::create([
            'student_id'     => $student->id,
            'date'           => $data['date'],
            'requested_time' => $data['requested_time'] . ':00',
            'reason'         => $data['reason'],
            'status'         => 'pending',
        ]);

        // Notify all guru/admin — any teacher can review
        NotificationService::broadcastToRole(
            roles:  ['guru', 'admin'],
            title:  'Izin Pulang Lebih Awal',
            body:   $student->name . ' mengajukan izin pulang lebih awal pada ' . Carbon::parse($data['date'])->isoFormat('D MMMM Y') . ' pukul ' . $data['requested_time'],
            type:   'info',
            url:    route('guru.early-checkout.index'),
        );

        return redirect()->route('siswa.early-checkout.index')
            ->with('success', 'Pengajuan izin pulang lebih awal berhasil dikirim. Menunggu persetujuan guru.');
    }

    public function destroy(EarlyCheckoutRequest $earlyCheckout): RedirectResponse
    {
        if ($earlyCheckout->student_id !== Auth::id() || ! $earlyCheckout->isPending()) {
            abort(403, 'Tidak dapat membatalkan pengajuan ini.');
        }

        $earlyCheckout->delete();

        return redirect()->route('siswa.early-checkout.index')
            ->with('success', 'Pengajuan berhasil dibatalkan.');
    }
}
