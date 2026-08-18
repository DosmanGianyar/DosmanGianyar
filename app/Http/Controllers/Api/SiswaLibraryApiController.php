<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LibraryLoan;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

use App\Models\LibraryVisit;

class SiswaLibraryApiController extends Controller
{
    /**
     * GET /api/v1/siswa/library/summary
     */
    public function summary(): JsonResponse
    {
        /** @var \App\Models\User $siswa */
        $siswa = Auth::user();
        $siswa->load('schoolClass');

        $loans = LibraryLoan::where('student_id', $siswa->id)->get();

        // Sync overdue statuses
        foreach ($loans as $loan) {
            if ($loan->status === 'borrowed' && Carbon::now()->startOfDay()->gt($loan->due_at)) {
                $loan->update(['status' => 'overdue']);
                $loan->status = 'overdue';
            }
        }

        $activeLoans   = $loans->whereIn('status', ['borrowed', 'overdue']);
        $returnedLoans = $loans->where('status', 'returned');
        $isClear       = $activeLoans->isEmpty();

        return response()->json([
            'student' => [
                'id'         => $siswa->id,
                'name'       => $siswa->name,
                'nis'        => $siswa->nis ?? '—',
                'nisn'       => $siswa->nisn ?? '—',
                'class_name' => $siswa->schoolClass?->name ?? '—',
            ],
            'is_clear'            => $isClear,
            'active_loans_count'   => $activeLoans->count(),
            'returned_loans_count' => $returnedLoans->count(),
            'total_loans_count'    => $loans->count(),
        ]);
    }

    /**
     * GET /api/v1/siswa/library/loans
     */
    public function loans(): JsonResponse
    {
        /** @var \App\Models\User $siswa */
        $siswa = Auth::user();

        $loans = LibraryLoan::where('student_id', $siswa->id)
            ->orderBy('created_at', 'desc')
            ->get();

        // Sync overdue statuses on the fly
        foreach ($loans as $loan) {
            if ($loan->status === 'borrowed' && Carbon::now()->startOfDay()->gt($loan->due_at)) {
                $loan->update(['status' => 'overdue']);
                $loan->status = 'overdue';
            }
        }

        $mappedLoans = $loans->map(fn (LibraryLoan $l) => [
            'id'           => $l->id,
            'book_title'   => $l->book_title,
            'book_code'    => $l->book_code,
            'phone_number' => $l->phone_number,
            'borrowed_at'  => $l->borrowed_at ? $l->borrowed_at->format('Y-m-d') : null,
            'due_at'       => $l->due_at ? $l->due_at->format('Y-m-d') : null,
            'returned_at'  => $l->returned_at ? $l->returned_at->format('Y-m-d H:i') : null,
            'status'       => $l->status,
            'status_label' => $l->statusLabel(),
            'is_overdue'   => $l->isOverdue(),
            'purpose'      => $l->purpose,
            'notes'        => $l->notes,
        ]);

        $activeLoans = $loans->whereIn('status', ['borrowed', 'overdue']);

        return response()->json([
            'is_clear' => $activeLoans->isEmpty(),
            'loans'    => $mappedLoans,
        ]);
    }

    /**
     * POST /api/v1/siswa/library/loans
     */
    public function storeLoan(Request $request): JsonResponse
    {
        try {
            /** @var \App\Models\User $siswa */
            $siswa = Auth::user();

            $validated = $request->validate([
                'book_title'     => 'required|string|max:255',
                'book_code'      => 'nullable|string|max:100',
                'phone_number'   => 'nullable|string|max:30',
                'borrowed_at'    => 'required|date',
                'due_at'         => 'required|date',
                'purpose_option' => 'nullable|string|max:100',
                'purpose_custom' => 'nullable|string|max:255',
                'notes'          => 'nullable|string|max:500',
            ]);

            $purposeOption = $validated['purpose_option'] ?? 'BELAJAR';
            $purpose = $purposeOption === 'LAINNYA'
                ? ($validated['purpose_custom'] ?: 'LAINNYA')
                : $purposeOption;

            $phoneNumber = ! empty($validated['phone_number']) ? $validated['phone_number'] : ($siswa->phone ?: '—');
            $borrowedAt  = Carbon::parse($validated['borrowed_at'])->toDateString();
            $dueAt       = Carbon::parse($validated['due_at'])->toDateString();

            $loan = LibraryLoan::create([
                'student_id'         => $siswa->id,
                'phone_number'       => $phoneNumber,
                'book_title'         => $validated['book_title'],
                'book_code'          => $validated['book_code'] ?? null,
                'borrowed_at'        => $borrowedAt,
                'due_at'             => $dueAt,
                'purpose'            => $purpose,
                'status'             => 'borrowed',
                'notes'              => $validated['notes'] ?? null,
                'created_by_user_id' => $siswa->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Peminjaman buku berhasil dicatat.',
                'loan'    => [
                    'id'           => $loan->id,
                    'book_title'   => $loan->book_title,
                    'book_code'    => $loan->book_code,
                    'borrowed_at'  => $loan->borrowed_at ? $loan->borrowed_at->format('Y-m-d') : null,
                    'due_at'       => $loan->due_at ? $loan->due_at->format('Y-m-d') : null,
                    'status'       => $loan->status,
                    'status_label' => $loan->statusLabel(),
                ],
            ], 201);
        } catch (ValidationException $e) {
            $firstError = collect($e->errors())->flatten()->first() ?? 'Data yang dimasukkan tidak valid.';
            return response()->json([
                'success' => false,
                'message' => $firstError,
                'errors'  => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan pinjaman buku: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * GET /api/v1/siswa/library/clearance
     */
    public function clearanceCard(): JsonResponse
    {
        /** @var \App\Models\User $siswa */
        $siswa = Auth::user();
        $siswa->load('schoolClass');

        $activeLoans = LibraryLoan::where('student_id', $siswa->id)
            ->whereIn('status', ['borrowed', 'overdue'])
            ->get();

        $isClear = $activeLoans->isEmpty();

        return response()->json([
            'student' => [
                'id'         => $siswa->id,
                'name'       => $siswa->name,
                'nis'        => $siswa->nis ?? '—',
                'nisn'       => $siswa->nisn ?? '—',
                'class_name' => $siswa->schoolClass?->name ?? '—',
            ],
            'is_clear'           => $isClear,
            'verification_url'   => url("/admin/library/clearance-card/{$siswa->id}"),
            'issue_date'         => Carbon::now()->isoFormat('D MMMM Y'),
            'active_loans_count' => $activeLoans->count(),
            'active_loans'       => $activeLoans->map(fn (LibraryLoan $l) => [
                'id'          => $l->id,
                'book_title'  => $l->book_title,
                'book_code'   => $l->book_code,
                'borrowed_at' => $l->borrowed_at ? $l->borrowed_at->format('Y-m-d') : null,
                'due_at'      => $l->due_at ? $l->due_at->format('Y-m-d') : null,
                'status'      => $l->status,
            ]),
        ]);
    }

    /**
     * GET /api/v1/siswa/library/visits
     */
    public function visits(): JsonResponse
    {
        /** @var \App\Models\User $siswa */
        $siswa = Auth::user();

        $visits = LibraryVisit::where('student_id', $siswa->id)
            ->orderBy('visited_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data'    => $visits->map(fn (LibraryVisit $v) => [
                'id'         => $v->id,
                'visited_at' => $v->visited_at ? $v->visited_at->format('Y-m-d H:i') : null,
                'purpose'    => $v->purpose,
                'notes'      => $v->notes,
                'created_at' => $v->created_at ? $v->created_at->format('Y-m-d H:i') : null,
            ]),
        ]);
    }

    /**
     * POST /api/v1/siswa/library/visits
     */
    public function storeVisit(Request $request): JsonResponse
    {
        /** @var \App\Models\User $siswa */
        $siswa = Auth::user();

        try {
            $validated = $request->validate([
                'qr_code'        => 'required|string',
                'visited_at'     => 'nullable|date',
                'purpose_option' => 'nullable|string|max:100',
                'purpose_custom' => 'nullable|string|max:255',
                'notes'          => 'nullable|string|max:500',
            ]);

            $qrCode = trim($validated['qr_code']);
            if (! str_contains($qrCode, 'SIMS_PERPUS_VISIT') && ! str_contains($qrCode, 'SIMS_LIBRARY_VISIT')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kode QR tidak valid! Pastikan Anda memindai Kode QR Kunjungan Resmi Perpustakaan.',
                ], 422);
            }

            $purposeOption = $validated['purpose_option'] ?? 'Membaca Buku Paket / Literasi';
            $purpose = $purposeOption === 'Lainnya'
                ? ($validated['purpose_custom'] ?: 'Lainnya')
                : $purposeOption;

            $visitedAt = ! empty($validated['visited_at']) ? Carbon::parse($validated['visited_at']) : Carbon::now();

            $visit = LibraryVisit::create([
                'student_id' => $siswa->id,
                'visited_at' => $visitedAt,
                'purpose'    => $purpose,
                'notes'      => $validated['notes'] ?? null,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Kehadiran kunjungan perpustakaan berhasil dicatat!',
                'data'    => [
                    'id'         => $visit->id,
                    'visited_at' => $visit->visited_at->format('Y-m-d H:i'),
                    'purpose'    => $visit->purpose,
                    'notes'      => $visit->notes,
                ],
            ], 201);
        } catch (ValidationException $e) {
            $firstError = collect($e->errors())->flatten()->first() ?? 'Data yang dimasukkan tidak valid.';
            return response()->json([
                'success' => false,
                'message' => $firstError,
                'errors'  => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mencatat kunjungan perpustakaan: ' . $e->getMessage(),
            ], 500);
        }
    }
}
