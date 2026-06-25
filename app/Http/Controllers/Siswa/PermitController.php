<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Jobs\SendWhatsappJob;
use App\Models\Permit;
use App\Services\WhatsappService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class PermitController extends Controller
{
    public function index(): View
    {
        $permits = Permit::where('student_id', Auth::id())
            ->latest()
            ->paginate(10);

        return view('siswa.permit.index', compact('permits'));
    }

    public function create(): View
    {
        return view('siswa.permit.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatePermit($request);

        $filePath = null;
        if ($request->hasFile('file')) {
            $filePath = $request->file('file')->store('permits', 'public');
        }

        Permit::create([
            'student_id' => Auth::id(),
            'type'       => $data['type'],
            'start_date' => $data['start_date'],
            'end_date'   => $data['end_date'],
            'reason'     => $data['reason'],
            'file'       => $filePath,
            'status'     => 'pending',
        ]);

        // Kirim notifikasi WA ke orang tua
        /** @var \App\Models\User $siswa */
        $siswa = Auth::user();
        if ($siswa->parent_phone) {
            $wa      = new WhatsappService();
            $kelas   = $siswa->schoolClass?->name ?? '-';
            $message = $wa->templatePermit(
                parentName  : $siswa->parent_name ?? 'Orang Tua',
                studentName : $siswa->name,
                className   : $kelas,
                typeLabel   : (new Permit(['type' => $data['type']]))->typeLabel(),
                startDate   : $data['start_date'],
                endDate     : $data['end_date'],
                reason      : $data['reason']
            );
            SendWhatsappJob::dispatch($siswa->parent_phone, $message);
        }

        $label = (new Permit(['type' => $data['type']]))->typeLabel();
        return redirect()->route('siswa.permit.index')
            ->with('success', "Pengajuan {$label} berhasil dikirim. Menunggu persetujuan guru.");
    }

    public function edit(Permit $permit): View|RedirectResponse
    {
        $this->authorizePermit($permit);
        return view('siswa.permit.edit', compact('permit'));
    }

    public function update(Request $request, Permit $permit): RedirectResponse
    {
        $this->authorizePermit($permit);
        $data = $this->validatePermit($request, isUpdate: true);

        if ($request->hasFile('file')) {
            if ($permit->file) {
                Storage::disk('public')->delete($permit->file);
            }
            $data['file'] = $request->file('file')->store('permits', 'public');
        }

        $permit->update($data);

        return redirect()->route('siswa.permit.index')
            ->with('success', 'Pengajuan berhasil diperbarui.');
    }

    public function destroy(Permit $permit): RedirectResponse
    {
        $this->authorizePermit($permit);

        if ($permit->file) {
            Storage::disk('public')->delete($permit->file);
        }
        $permit->delete();

        return redirect()->route('siswa.permit.index')
            ->with('success', 'Pengajuan berhasil dihapus.');
    }

    private function authorizePermit(Permit $permit): void
    {
        if ($permit->student_id !== Auth::id() || ! $permit->isPending()) {
            abort(403, 'Tidak dapat mengubah pengajuan yang sudah diproses.');
        }
    }

    private function validatePermit(Request $request, bool $isUpdate = false): array
    {
        $dateRule = $isUpdate ? 'required|date' : 'required|date|after_or_equal:today';
        return $request->validate([
            'type'       => 'required|in:izin,sakit,dispensasi',
            'start_date' => $dateRule,
            'end_date'   => 'required|date|after_or_equal:start_date',
            'reason'     => 'required|string|max:500',
            'file'       => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);
    }
}
