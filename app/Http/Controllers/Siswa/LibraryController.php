<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\LibraryLoan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Carbon\Carbon;

use App\Models\LibraryVisit;

class LibraryController extends Controller
{
    public function index(Request $request): View
    {
        /** @var \App\Models\User $siswa */
        $siswa = Auth::user();

        $loans = LibraryLoan::where('student_id', $siswa->id)
            ->orderBy('created_at', 'desc')
            ->get();

        // Update overdue status on the fly if past due date
        foreach ($loans as $loan) {
            if ($loan->status === 'borrowed' && Carbon::now()->startOfDay()->gt($loan->due_at)) {
                $loan->update(['status' => 'overdue']);
            }
        }

        $activeLoans   = $loans->whereIn('status', ['borrowed', 'overdue']);
        $returnedLoans = $loans->where('status', 'returned');

        $isClear = $activeLoans->isEmpty();

        return view('siswa.library.index', compact('siswa', 'loans', 'activeLoans', 'returnedLoans', 'isClear'));
    }

    public function visitIndex(Request $request): View
    {
        /** @var \App\Models\User $siswa */
        $siswa = Auth::user();

        $visits = LibraryVisit::where('student_id', $siswa->id)
            ->orderBy('visited_at', 'desc')
            ->get();

        return view('siswa.library.visit', compact('siswa', 'visits'));
    }

    public function storeVisit(Request $request): RedirectResponse
    {
        /** @var \App\Models\User $siswa */
        $siswa = Auth::user();

        $validated = $request->validate([
            'qr_code'        => 'required|string',
            'visited_at'     => 'required|date',
            'purpose_option' => 'nullable|string|max:100',
            'purpose_custom' => 'nullable|string|max:255',
            'notes'          => 'nullable|string|max:500',
        ], [
            'qr_code.required'    => 'Kode QR Kunjungan wajib di-scan.',
            'visited_at.required' => 'Tanggal & waktu kunjungan wajib diisi.',
        ]);

        $qrCode = trim($validated['qr_code']);
        if (! str_contains($qrCode, 'SIMS_PERPUS_VISIT') && ! str_contains($qrCode, 'SIMS_LIBRARY_VISIT')) {
            return back()->withInput()->with('error', 'Kode QR tidak valid! Pastikan Anda memindai Kode QR Kunjungan Resmi Perpustakaan.');
        }

        $purposeOption = $validated['purpose_option'] ?? 'Membaca Buku Paket / Literasi';
        $purpose = $purposeOption === 'Lainnya'
            ? ($validated['purpose_custom'] ?: 'Lainnya')
            : $purposeOption;

        LibraryVisit::create([
            'student_id' => $siswa->id,
            'visited_at' => $validated['visited_at'],
            'purpose'    => $purpose,
            'notes'      => $validated['notes'] ?? null,
        ]);

        return redirect()->route('siswa.library.visit')
            ->with('success', 'Kehadiran kunjungan perpustakaan berhasil dicatat! Selamat membaca.');
    }

    public function store(Request $request): RedirectResponse
    {
        /** @var \App\Models\User $siswa */
        $siswa = Auth::user();

        $validated = $request->validate([
            'book_title'     => 'required|string|max:255',
            'book_code'      => 'nullable|string|max:100',
            'phone_number'   => 'nullable|string|max:30',
            'borrowed_at'    => 'required|date',
            'due_at'         => 'required|date|after_or_equal:borrowed_at',
            'purpose_option' => 'nullable|string|max:100',
            'purpose_custom' => 'nullable|string|max:255',
            'notes'          => 'nullable|string|max:500',
        ], [
            'book_title.required'   => 'Judul buku wajib diisi.',
            'due_at.after_or_equal' => 'Tanggal batas kembali harus sama atau setelah tanggal pinjam.',
        ]);

        $purposeOption = $validated['purpose_option'] ?? 'BELAJAR';
        $purpose = $purposeOption === 'LAINNYA'
            ? ($validated['purpose_custom'] ?: 'LAINNYA')
            : $purposeOption;

        $phoneNumber = ! empty($validated['phone_number']) ? $validated['phone_number'] : ($siswa->phone ?: '—');

        LibraryLoan::create([
            'student_id'          => $siswa->id,
            'phone_number'        => $phoneNumber,
            'book_title'          => $validated['book_title'],
            'book_code'           => $validated['book_code'] ?? null,
            'borrowed_at'         => $validated['borrowed_at'],
            'due_at'              => $validated['due_at'],
            'purpose'             => $purpose,
            'status'              => 'borrowed',
            'notes'               => $validated['notes'] ?? null,
            'created_by_user_id'  => $siswa->id,
        ]);

        return redirect()->route('siswa.library.index')
            ->with('success', 'Peminjaman buku berhasil dicatat. Silakan serahkan buku saat pengembalian.');
    }

    public function clearanceCard(Request $request): View
    {
        /** @var \App\Models\User $siswa */
        $siswa = Auth::user();
        $siswa->load('schoolClass');

        $activeLoans = LibraryLoan::where('student_id', $siswa->id)
            ->whereIn('status', ['borrowed', 'overdue'])
            ->get();

        $isClear = $activeLoans->isEmpty();

        return view('siswa.library.clearance_card', compact('siswa', 'activeLoans', 'isClear'));
    }

    public function adminClearanceCard(User $user): View
    {
        $siswa = $user;
        $siswa->load('schoolClass');

        $activeLoans = LibraryLoan::where('student_id', $siswa->id)
            ->whereIn('status', ['borrowed', 'overdue'])
            ->get();

        $isClear = $activeLoans->isEmpty();

        return view('siswa.library.clearance_card', compact('siswa', 'activeLoans', 'isClear'));
    }
}
