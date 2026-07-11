<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\AssetLoan;
use App\Models\DamageReport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class GuruSarprasApiController extends Controller
{
    // GET /api/v1/guru/sarpras — dashboard stats
    public function stats(): JsonResponse
    {
        $totalAssets    = Asset::count();
        $baik           = Asset::where('condition', 'baik')->count();
        $rusakRingan    = Asset::where('condition', 'rusak_ringan')->count();
        $rusakBerat     = Asset::where('condition', 'rusak_berat')->count();
        $pendingDamage  = DamageReport::where('status', 'pending')->count();
        $pendingLoans   = AssetLoan::where('status', 'pending')->count();
        $activeLoans    = AssetLoan::where('status', 'active')->count();
        $myLoans        = AssetLoan::where('user_id', Auth::id())
            ->whereIn('status', ['pending', 'approved', 'active'])
            ->count();

        return response()->json([
            'total_assets'   => $totalAssets,
            'baik'           => $baik,
            'rusak_ringan'   => $rusakRingan,
            'rusak_berat'    => $rusakBerat,
            'pending_damage' => $pendingDamage,
            'pending_loans'  => $pendingLoans,
            'active_loans'   => $activeLoans,
            'my_loans'       => $myLoans,
        ]);
    }

    // GET /api/v1/guru/sarpras/assets?category=&condition=&q=
    public function assets(Request $request): JsonResponse
    {
        $query = Asset::with('room:id,name')->orderBy('name');

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }
        if ($request->filled('condition')) {
            $query->where('condition', $request->condition);
        }
        if ($request->filled('q')) {
            $q = $request->q;
            $query->where('name', 'like', "%{$q}%");
        }

        $paginated = $query->paginate(20);

        return response()->json([
            'data' => collect($paginated->items())->map(fn ($a) => [
                'id'            => $a->id,
                'name'          => $a->name,
                'category'      => $a->category,
                'category_label'=> $a->categoryLabel(),
                'condition'     => $a->condition,
                'condition_label'=> $a->conditionLabel(),
                'room_name'     => $a->room?->name,
                'quantity'      => $a->quantity,
                'purchase_year' => $a->purchase_year,
                'description'   => $a->description,
            ]),
            'current_page' => $paginated->currentPage(),
            'last_page'    => $paginated->lastPage(),
            'total'        => $paginated->total(),
        ]);
    }

    // GET /api/v1/guru/sarpras/damage?status=&page=
    public function damage(Request $request): JsonResponse
    {
        $query = DamageReport::with([
            'asset:id,name,category',
            'reporter:id,name',
            'handler:id,name',
        ])->orderByDesc('created_at');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // teacher only sees their own reports + all if they have sarpras role
        $user = Auth::user();
        if (!in_array($user->role, ['admin', 'sarpras', 'kepala_sekolah'])) {
            $query->where('reporter_id', $user->id);
        }

        $paginated = $query->paginate(20);

        return response()->json([
            'data' => collect($paginated->items())->map(fn ($r) => [
                'id'              => $r->id,
                'asset_id'        => $r->asset_id,
                'asset_name'      => $r->asset?->name,
                'asset_category'  => $r->asset?->category,
                'reporter_name'   => $r->reporter?->name,
                'handler_name'    => $r->handler?->name,
                'description'     => $r->description,
                'status'          => $r->status,
                'status_label'    => $r->statusLabel(),
                'resolution_note' => $r->resolution_note,
                'days_open'       => $r->daysOpen(),
                'created_at'      => $r->created_at?->format('Y-m-d'),
            ]),
            'current_page' => $paginated->currentPage(),
            'last_page'    => $paginated->lastPage(),
            'total'        => $paginated->total(),
        ]);
    }

    // POST /api/v1/guru/sarpras/damage
    public function storeDamage(Request $request): JsonResponse
    {
        $request->validate([
            'asset_id'    => 'required|exists:assets,id',
            'description' => 'required|string|max:1000',
        ]);

        DamageReport::create([
            'asset_id'    => $request->asset_id,
            'reporter_id' => Auth::id(),
            'description' => $request->description,
            'status'      => 'pending',
        ]);

        return response()->json(['message' => 'Laporan kerusakan berhasil dikirim.'], 201);
    }

    // GET /api/v1/guru/sarpras/loans?status=&page=
    public function loans(Request $request): JsonResponse
    {
        $user  = Auth::user();
        $query = AssetLoan::with([
            'asset:id,name,category',
            'user:id,name',
            'approver:id,name',
        ])->orderByDesc('created_at');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // teacher sees only their own loans
        if (!in_array($user->role, ['admin', 'sarpras', 'kepala_sekolah'])) {
            $query->where('user_id', $user->id);
        }

        $paginated = $query->paginate(20);

        return response()->json([
            'data' => collect($paginated->items())->map(fn ($l) => [
                'id'             => $l->id,
                'asset_id'       => $l->asset_id,
                'asset_name'     => $l->asset?->name,
                'asset_category' => $l->asset?->category,
                'borrower_name'  => $l->user?->name,
                'approver_name'  => $l->approver?->name,
                'purpose'        => $l->purpose,
                'start_date'     => $l->start_date?->format('Y-m-d'),
                'end_date'       => $l->end_date?->format('Y-m-d'),
                'status'         => $l->status,
                'status_label'   => $l->statusLabel(),
                'rejection_note' => $l->rejection_note,
                'created_at'     => $l->created_at?->format('Y-m-d'),
            ]),
            'current_page' => $paginated->currentPage(),
            'last_page'    => $paginated->lastPage(),
            'total'        => $paginated->total(),
        ]);
    }

    // POST /api/v1/guru/sarpras/loans
    public function storeLoan(Request $request): JsonResponse
    {
        $request->validate([
            'asset_id'   => 'required|exists:assets,id',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date'   => 'required|date|after_or_equal:start_date',
            'purpose'    => 'required|string|max:500',
        ]);

        AssetLoan::create([
            'asset_id'   => $request->asset_id,
            'user_id'    => Auth::id(),
            'start_date' => $request->start_date,
            'end_date'   => $request->end_date,
            'purpose'    => $request->purpose,
            'status'     => 'pending',
        ]);

        return response()->json(['message' => 'Permintaan peminjaman berhasil dikirim.'], 201);
    }

    // PATCH /api/v1/guru/sarpras/loans/{id}/return
    public function returnLoan(int $id): JsonResponse
    {
        $loan = AssetLoan::findOrFail($id);

        if ($loan->user_id !== Auth::id()) {
            return response()->json(['message' => 'Tidak diizinkan.'], 403);
        }

        if (!in_array($loan->status, ['approved', 'active'])) {
            return response()->json(['message' => 'Status peminjaman tidak valid.'], 422);
        }

        $loan->update(['status' => 'returned']);

        return response()->json(['message' => 'Peminjaman berhasil dikembalikan.']);
    }

    // GET /api/v1/guru/sarpras/categories
    public function categories(): JsonResponse
    {
        return response()->json([
            'conditions' => [
                ['value' => 'baik',         'label' => 'Baik'],
                ['value' => 'rusak_ringan',  'label' => 'Rusak Ringan'],
                ['value' => 'rusak_berat',   'label' => 'Rusak Berat'],
            ],
            'categories' => [
                ['value' => 'perpus',    'label' => 'Perpustakaan'],
                ['value' => 'sarana',    'label' => 'Sarana'],
                ['value' => 'prasarana', 'label' => 'Prasarana'],
            ],
        ]);
    }
}
