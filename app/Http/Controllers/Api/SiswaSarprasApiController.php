<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\AssetLoan;
use App\Models\DamageReport;
use App\Models\Room;
use App\Services\ImageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SiswaSarprasApiController extends Controller
{
    // GET /api/v1/siswa/sarpras/summary
    public function summary(): JsonResponse
    {
        $userId = Auth::id();

        $activeLoans    = AssetLoan::where('user_id', $userId)->whereIn('status', ['pending', 'approved', 'active'])->count();
        $returnedLoans  = AssetLoan::where('user_id', $userId)->where('status', 'returned')->count();
        $damagePending  = DamageReport::where('reporter_id', $userId)->whereIn('status', ['pending', 'in_progress'])->count();
        $damageTotal    = DamageReport::where('reporter_id', $userId)->count();

        return response()->json([
            'active_loans'    => $activeLoans,
            'returned_loans'  => $returnedLoans,
            'damage_pending'  => $damagePending,
            'damage_total'    => $damageTotal,
        ]);
    }

    // GET /api/v1/siswa/sarpras/catalog?q=&category=&room_id=
    public function catalog(Request $request): JsonResponse
    {
        $query = Asset::with('room')->orderBy('name');

        if ($request->filled('q')) {
            $query->where('name', 'like', '%' . $request->q . '%');
        }
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }
        if ($request->filled('room_id')) {
            $query->where('room_id', $request->room_id);
        }

        $assets = $query->limit(100)->get()->map(fn (Asset $a) => [
            'id'             => $a->id,
            'code'           => $a->code,
            'name'           => $a->name,
            'category'       => $a->category,
            'condition'      => $a->condition,
            'qr_code'        => $a->qr_code,
            'room_name'      => $a->room?->name ?? '—',
            'is_borrowable'  => $a->condition !== 'rusak_berat',
        ]);

        $rooms = Room::orderBy('name')->get(['id', 'name']);

        return response()->json([
            'assets' => $assets,
            'rooms'  => $rooms,
        ]);
    }

    // GET /api/v1/siswa/sarpras/loans
    public function myLoans(): JsonResponse
    {
        $loans = AssetLoan::with(['asset.room'])
            ->where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn (AssetLoan $l) => [
                'id'            => $l->id,
                'asset_name'    => $l->asset?->name ?? 'Aset',
                'asset_code'    => $l->asset?->code ?? '',
                'room_name'     => $l->asset?->room?->name ?? '—',
                'loan_date'     => $l->loan_date ? $l->loan_date->toDateString() : '—',
                'return_date'   => $l->return_date ? $l->return_date->toDateString() : '—',
                'actual_return' => $l->actual_return_date ? $l->actual_return_date->toDateString() : null,
                'status'        => $l->status,
                'status_label'  => match ($l->status) {
                    'pending'   => 'Menunggu Persetujuan',
                    'approved', 'active' => 'Dipinjam',
                    'returned'  => 'Dikembalikan',
                    'rejected'  => 'Ditolak',
                    default     => $l->status,
                },
                'notes'         => $l->notes,
            ]);

        return response()->json($loans);
    }

    // POST /api/v1/siswa/sarpras/loans
    public function storeLoan(Request $request): JsonResponse
    {
        $request->validate([
            'asset_id'    => 'required|exists:assets,id',
            'loan_date'   => 'required|date',
            'return_date' => 'required|date|after_or_equal:loan_date',
            'notes'       => 'nullable|string|max:500',
        ]);

        $asset = Asset::find($request->asset_id);
        if ($asset->condition === 'rusak_berat') {
            return response()->json(['message' => 'Aset dalam kondisi rusak berat dan tidak dapat dipinjam.'], 400);
        }

        $existingLoan = AssetLoan::where('asset_id', $asset->id)
            ->where('user_id', Auth::id())
            ->whereIn('status', ['pending', 'approved', 'active'])
            ->first();

        if ($existingLoan) {
            return response()->json(['message' => 'Anda sudah memiliki pengajuan pinjaman aktif untuk aset ini.'], 400);
        }

        $loan = AssetLoan::create([
            'asset_id'    => $asset->id,
            'user_id'     => Auth::id(),
            'loan_date'   => $request->loan_date,
            'return_date' => $request->return_date,
            'notes'       => $request->notes,
            'status'      => 'pending',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pengajuan peminjaman barang berhasil dikirim.',
            'loan_id' => $loan->id,
        ]);
    }

    // GET /api/v1/siswa/sarpras/damage-reports
    public function myDamageReports(): JsonResponse
    {
        $reports = DamageReport::with(['asset', 'room'])
            ->where('reporter_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn (DamageReport $r) => [
                'id'            => $r->id,
                'title'         => $r->title,
                'description'   => $r->description,
                'location'      => $r->asset?->name ?? ($r->room?->name ?? 'Fasilitas Sekolah'),
                'photo_url'     => $r->photo_url,
                'status'        => $r->status,
                'status_label'  => match ($r->status) {
                    'pending'     => 'Menunggu Penanganan',
                    'in_progress' => 'Sedang Diperbaiki',
                    'resolved'    => 'Selesai Diperbaiki',
                    'rejected'    => 'Ditolak',
                    default       => $r->status,
                },
                'date'          => $r->created_at->toDateString(),
            ]);

        return response()->json($reports);
    }

    // POST /api/v1/siswa/sarpras/damage-reports
    public function storeDamageReport(Request $request): JsonResponse
    {
        $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'required|string',
            'room_id'     => 'nullable|exists:rooms,id',
            'asset_id'    => 'nullable|exists:assets,id',
            'photo'       => 'nullable|image|max:5120',
        ]);

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = ImageService::compressAndStore($request->file('photo'), 'damage-reports', 1200, 80);
        }

        $report = DamageReport::create([
            'reporter_id' => Auth::id(),
            'title'       => $request->title,
            'description' => $request->description,
            'room_id'     => $request->room_id,
            'asset_id'    => $request->asset_id,
            'photo'       => $photoPath,
            'status'      => 'pending',
        ]);

        return response()->json([
            'success'   => true,
            'message'   => 'Laporan kerusakan fasilitas berhasil dikirim.',
            'report_id' => $report->id,
        ]);
    }
}
