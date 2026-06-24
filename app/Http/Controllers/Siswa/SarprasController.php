<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\AssetLoan;
use App\Models\DamageReport;
use App\Models\Room;
use App\Services\ImageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SarprasController extends Controller
{
    public function catalog(Request $request): View
    {
        $query = Asset::with('room')->orderBy('name');

        if ($request->filled('q')) {
            $query->where('name', 'like', '%' . $request->q . '%');
        }
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }
        if ($request->filled('condition')) {
            $query->where('condition', $request->condition);
        }
        if ($request->filled('room_id')) {
            $query->where('room_id', $request->room_id);
        }

        $assets = $query->paginate(20)->withQueryString();
        $rooms  = Room::orderBy('name')->get();

        return view('siswa.sarpras.catalog', compact('assets', 'rooms'));
    }

    public function scan(): View
    {
        return view('siswa.sarpras.scan');
    }

    public function show(string $qrCode): View
    {
        $asset = Asset::with('room', 'loans', 'damageReports', 'maintenanceLogs')
            ->where('qr_code', $qrCode)
            ->firstOrFail();

        $activeLoans   = $asset->loans()->whereIn('status', ['approved', 'active'])->count();
        $myActiveLoan  = $asset->loans()->where('user_id', Auth::id())->whereIn('status', ['pending', 'approved', 'active'])->first();
        $pendingDamage = $asset->damageReports()->whereIn('status', ['pending', 'in_progress'])->count();

        return view('siswa.sarpras.show', compact('asset', 'activeLoans', 'myActiveLoan', 'pendingDamage'));
    }

    public function createDamage(Asset $asset): View
    {
        return view('siswa.sarpras.damage-create', compact('asset'));
    }

    public function storeDamage(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'asset_id'    => 'required|exists:assets,id',
            'description' => 'required|string|max:500',
            'photo'       => 'required|image|max:5120',
        ]);

        $path = ImageService::store($request->file('photo'), 'damage-reports', maxWidth: 1280, quality: 80);

        DamageReport::create([
            'asset_id'    => $data['asset_id'],
            'reporter_id' => Auth::id(),
            'description' => $data['description'],
            'photo'       => $path,
        ]);

        return redirect()->route('siswa.sarpras.asset.show', Asset::find($data['asset_id'])->qr_code)
            ->with('success', 'Laporan kerusakan berhasil dikirim. Terima kasih!');
    }

    public function createLoan(Asset $asset): View
    {
        return view('siswa.sarpras.loan-create', compact('asset'));
    }

    public function storeLoan(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'asset_id'   => 'required|exists:assets,id',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date'   => 'required|date|after_or_equal:start_date',
            'purpose'    => 'required|string|max:255',
        ]);

        $conflict = AssetLoan::where('asset_id', $data['asset_id'])
            ->whereIn('status', ['approved', 'active'])
            ->where('start_date', '<=', $data['end_date'])
            ->where('end_date', '>=', $data['start_date'])
            ->exists();

        if ($conflict) {
            return back()->withErrors(['start_date' => 'Aset sudah dipinjam pada periode tersebut.'])->withInput();
        }

        AssetLoan::create([
            ...$data,
            'user_id' => Auth::id(),
        ]);

        return redirect()->route('siswa.sarpras.loans')
            ->with('success', 'Permintaan peminjaman dikirim, menunggu persetujuan guru.');
    }

    public function myLoans(): View
    {
        $loans = AssetLoan::with('asset')
            ->where('user_id', Auth::id())
            ->latest()
            ->paginate(15);

        return view('siswa.sarpras.loans', compact('loans'));
    }
}
