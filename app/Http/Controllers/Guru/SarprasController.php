<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\AssetLoan;
use App\Models\ConductCategory;
use App\Models\ConductLog;
use App\Models\DamageReport;
use App\Models\MaintenanceLog;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SarprasController extends Controller
{
    public function index(): View
    {
        $stats = [
            'total_assets'    => Asset::count(),
            'baik'            => Asset::where('condition', 'baik')->count(),
            'rusak_ringan'    => Asset::where('condition', 'rusak_ringan')->count(),
            'rusak_berat'     => Asset::where('condition', 'rusak_berat')->count(),
            'pending_damage'  => DamageReport::where('status', 'pending')->count(),
            'pending_loans'   => AssetLoan::where('status', 'pending')->count(),
            'overdue_damage'  => DamageReport::whereIn('status', ['pending', 'in_progress'])
                ->where(fn($q) => $q
                    ->where(fn($q2) => $q2->where('status', 'pending')->where('created_at', '<', now()->subDays(3)))
                    ->orWhere(fn($q2) => $q2->where('status', 'in_progress')->where('created_at', '<', now()->subDays(7)))
                )->count(),
        ];

        $recentDamage = DamageReport::with('asset', 'reporter')
            ->where('status', 'pending')
            ->latest()
            ->take(5)
            ->get();

        $recentLoans = AssetLoan::with('asset', 'user')
            ->where('status', 'pending')
            ->latest()
            ->take(5)
            ->get();

        return view('guru.sarpras.index', compact('stats', 'recentDamage', 'recentLoans'));
    }

    public function damage(Request $request): View
    {
        $query = DamageReport::with('asset', 'reporter', 'handler')->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $reports           = $query->paginate(20)->withQueryString();
        $conductCategories = ConductCategory::active()->orderBy('type')->orderBy('name')->get();

        return view('guru.sarpras.damage', compact('reports', 'conductCategories'));
    }

    public function progressDamage(DamageReport $report): RedirectResponse
    {
        $report->update(['status' => 'in_progress', 'handled_by' => Auth::id()]);
        return back()->with('success', 'Laporan ditandai sedang ditangani.');
    }

    public function resolveDamage(Request $request, DamageReport $report): RedirectResponse
    {
        $request->validate([
            'new_condition'      => 'nullable|in:baik,rusak_ringan,rusak_berat',
            'resolution_note'    => 'nullable|string|max:500',
            'conduct_category_id'=> 'nullable|exists:conduct_categories,id',
            'conduct_note'       => 'nullable|string|max:500',
        ]);

        $report->update([
            'status'          => 'resolved',
            'handled_by'      => Auth::id(),
            'resolution_note' => $request->resolution_note,
        ]);

        if ($request->filled('new_condition')) {
            $report->asset->update(['condition' => $request->new_condition]);
        }

        // Bridge: optionally log conduct for the reporting siswa
        if ($request->filled('conduct_category_id')) {
            $reporter = $report->reporter;
            if ($reporter && in_array($reporter->role, ['siswa', 'siswa_pengelola'])) {
                $category = ConductCategory::find($request->conduct_category_id);
                if ($category) {
                    ConductLog::create([
                        'student_id'  => $reporter->id,
                        'teacher_id'  => Auth::id(),
                        'category_id' => $category->id,
                        'point'       => $category->point_value,
                        'note'        => $request->conduct_note
                            ?: "Terkait laporan kerusakan: {$report->asset->name}",
                    ]);
                }
            }
        }

        return back()->with('success', 'Laporan kerusakan diselesaikan.');
    }

    public function loans(Request $request): View
    {
        $query = AssetLoan::with('asset', 'user', 'approver')->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $loans = $query->paginate(20)->withQueryString();

        return view('guru.sarpras.loans', compact('loans'));
    }

    public function approveLoan(AssetLoan $loan): RedirectResponse
    {
        $loan->update(['status' => 'approved', 'approved_by' => Auth::id()]);

        NotificationService::send(
            $loan->user_id,
            'Peminjaman Disetujui',
            "Permintaan peminjaman {$loan->asset->name} kamu telah disetujui. Periode: {$loan->start_date->isoFormat('D MMM')} – {$loan->end_date->isoFormat('D MMM Y')}.",
            'success',
            route('siswa.sarpras.loans'),
        );

        return back()->with('success', 'Peminjaman disetujui.');
    }

    public function rejectLoan(Request $request, AssetLoan $loan): RedirectResponse
    {
        $loan->update([
            'status'         => 'rejected',
            'approved_by'    => Auth::id(),
            'rejection_note' => $request->rejection_note,
        ]);

        NotificationService::send(
            $loan->user_id,
            'Peminjaman Ditolak',
            "Permintaan peminjaman {$loan->asset->name} kamu ditolak." . ($request->rejection_note ? " Alasan: {$request->rejection_note}" : ''),
            'warning',
            route('siswa.sarpras.loans'),
        );

        return back()->with('success', 'Peminjaman ditolak.');
    }

    public function returnLoan(AssetLoan $loan): RedirectResponse
    {
        $loan->update(['status' => 'returned']);
        return back()->with('success', 'Aset telah dikembalikan.');
    }

    public function storeMaintenance(Request $request, Asset $asset): RedirectResponse
    {
        $data = $request->validate([
            'tech_name' => 'required|string|max:100',
            'date'      => 'required|date',
            'cost'      => 'nullable|numeric|min:0',
            'note'      => 'required|string',
        ]);

        MaintenanceLog::create([
            ...$data,
            'asset_id'    => $asset->id,
            'cost'        => $data['cost'] ?? 0,
            'recorded_by' => Auth::id(),
        ]);

        return back()->with('success', 'Log perawatan disimpan.');
    }
}
