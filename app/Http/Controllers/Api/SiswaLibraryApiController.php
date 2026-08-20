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
            'book_nisb'    => $l->book_nisb,
            'book_author'  => $l->book_author,
            'phone_number' => $l->phone_number,
            'borrowed_at'  => static::formatDate($l->borrowed_at, 'Y-m-d'),
            'due_at'       => static::formatDate($l->due_at, 'Y-m-d'),
            'returned_at'  => static::formatDate($l->returned_at, 'Y-m-d H:i'),
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

    private static function formatDate($date, string $format = 'Y-m-d'): ?string
    {
        if (empty($date)) return null;
        if (is_string($date)) {
            try {
                return Carbon::parse($date)->format($format);
            } catch (\Throwable $e) {
                return $date;
            }
        }
        if ($date instanceof \DateTimeInterface) {
            return $date->format($format);
        }
        return null;
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
                'book_nisb'      => 'nullable|string|max:100',
                'book_author'    => 'nullable|string|max:255',
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

            try {
                $borrowedAt = Carbon::parse($validated['borrowed_at'])->toDateString();
            } catch (\Throwable $e) {
                $borrowedAt = now()->toDateString();
            }

            try {
                $dueAt = Carbon::parse($validated['due_at'])->toDateString();
            } catch (\Throwable $e) {
                $dueAt = now()->addDays(7)->toDateString();
            }

            $loan = LibraryLoan::create([
                'student_id'         => $siswa->id,
                'phone_number'       => $phoneNumber,
                'book_title'         => $validated['book_title'],
                'book_code'          => $validated['book_code'] ?? null,
                'book_nisb'          => $validated['book_nisb'] ?? null,
                'book_author'        => $validated['book_author'] ?? null,
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
                    'borrowed_at'  => static::formatDate($loan->borrowed_at, 'Y-m-d'),
                    'due_at'       => static::formatDate($loan->due_at, 'Y-m-d'),
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
     * GET /api/v1/siswa/library/catalog
     */
    public function catalog(Request $request): JsonResponse
    {
        $search   = $request->input('search');
        $category = $request->input('category');

        $query = \App\Models\LibraryBook::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('author', 'like', "%{$search}%")
                  ->orWhere('isbn', 'like', "%{$search}%")
                  ->orWhere('book_code', 'like', "%{$search}%");
            });
        }

        if ($category && $category !== 'all') {
            $query->where('category', $category);
        }

        $books = $query->orderBy('title')->get();

        return response()->json([
            'success' => true,
            'data'    => $books->map(fn (\App\Models\LibraryBook $b) => [
                'id'              => $b->id,
                'book_code'       => $b->book_code,
                'isbn'            => $b->isbn,
                'title'           => $b->title,
                'author'          => $b->author,
                'publisher'       => $b->publisher,
                'publish_year'    => $b->publish_year,
                'category'        => $b->category,
                'total_stock'     => $b->total_stock,
                'borrowed_count'  => $b->borrowed_count,
                'available_stock' => $b->available_stock,
                'shelf_location'  => $b->shelf_location,
                'cover_url'       => $b->cover_url,
                'description'     => $b->description,
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

            $qrCodeUpper = strtoupper(trim($validated['qr_code']));
            if (! str_contains($qrCodeUpper, 'PERPUSTAKAAN DOSMAN') && ! str_contains($qrCodeUpper, 'PERPUSTAKAAN_DOSMAN') && ! str_contains($qrCodeUpper, 'SIMAK DOSMAN') && ! str_contains($qrCodeUpper, 'SIMAK_DOSMAN') && ! str_contains($qrCodeUpper, 'SIMS_PERPUS_VISIT') && ! str_contains($qrCodeUpper, 'SIMS_LIBRARY_VISIT')) {
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
