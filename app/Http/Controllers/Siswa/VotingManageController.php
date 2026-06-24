<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\Candidate;
use App\Models\VotingSession;
use App\Services\ImageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class VotingManageController extends Controller
{
    private function authorizeSession(VotingSession $session): void
    {
        if ($session->created_by !== Auth::id()) {
            abort(403);
        }
    }

    public function index(): View
    {
        $this->checkRole();
        $sessions = VotingSession::where('created_by', Auth::id())
            ->withCount(['candidates', 'votes'])
            ->latest()
            ->get();

        return view('siswa.voting.manage.index', compact('sessions'));
    }

    public function create(): View
    {
        $this->checkRole();
        return view('siswa.voting.manage.form', ['session' => null]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->checkRole();

        $data = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'start_time'  => 'required|date|after:now',
            'end_time'    => 'required|date|after:start_time',
        ]);

        $data['created_by'] = Auth::id();
        $data['status']     = 'draft';

        $session = VotingSession::create($data);

        return redirect()->route('siswa.voting.manage.show', $session)
            ->with('success', 'Sesi voting berhasil dibuat. Tambahkan kandidat sekarang.');
    }

    public function edit(VotingSession $session): View
    {
        $this->checkRole();
        $this->authorizeSession($session);

        if (! $session->isDraft()) {
            abort(403, 'Sesi yang sudah aktif tidak dapat diedit.');
        }

        return view('siswa.voting.manage.form', compact('session'));
    }

    public function update(Request $request, VotingSession $session): RedirectResponse
    {
        $this->checkRole();
        $this->authorizeSession($session);

        if (! $session->isDraft()) {
            return back()->with('error', 'Sesi yang sudah aktif tidak dapat diedit.');
        }

        $data = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'start_time'  => 'required|date',
            'end_time'    => 'required|date|after:start_time',
        ]);

        $session->update($data);

        return redirect()->route('siswa.voting.manage.show', $session)
            ->with('success', 'Sesi voting berhasil diperbarui.');
    }

    public function show(VotingSession $session): View
    {
        $this->checkRole();
        $this->authorizeSession($session);

        $session->load(['candidates.votes', 'votes']);
        $totalVotes = $session->votes()->count();

        return view('siswa.voting.manage.show', compact('session', 'totalVotes'));
    }

    public function storeCandidate(Request $request, VotingSession $session): RedirectResponse
    {
        $this->checkRole();
        $this->authorizeSession($session);

        if (! $session->isDraft()) {
            return back()->with('error', 'Kandidat hanya bisa ditambah saat status Draft.');
        }

        $data = $request->validate([
            'name'   => 'required|string|max:255',
            'vision' => 'nullable|string|max:1000',
            'photo'  => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('photo')) {
            $data['photo'] = ImageService::store($request->file('photo'), 'candidates', maxWidth: 800, quality: 85);
        }

        $data['voting_session_id'] = $session->id;
        $data['order']             = $session->candidates()->count();

        Candidate::create($data);

        return back()->with('success', "Kandidat {$data['name']} berhasil ditambahkan.");
    }

    public function removeCandidate(VotingSession $session, Candidate $candidate): RedirectResponse
    {
        $this->checkRole();
        $this->authorizeSession($session);

        if (! $session->isDraft()) {
            return back()->with('error', 'Kandidat tidak bisa dihapus setelah voting aktif.');
        }

        if ($candidate->voting_session_id !== $session->id) {
            abort(403);
        }

        $candidate->delete();

        return back()->with('success', 'Kandidat berhasil dihapus.');
    }

    public function activate(VotingSession $session): RedirectResponse
    {
        $this->checkRole();
        $this->authorizeSession($session);

        if (! $session->isDraft()) {
            return back()->with('error', 'Sesi sudah aktif atau selesai.');
        }

        if ($session->candidates()->count() < 2) {
            return back()->with('error', 'Minimal 2 kandidat diperlukan untuk mengaktifkan voting.');
        }

        $session->update(['status' => 'active']);

        return back()->with('success', 'Sesi voting berhasil diaktifkan!');
    }

    public function close(VotingSession $session): RedirectResponse
    {
        $this->checkRole();
        $this->authorizeSession($session);

        if ($session->isClosed()) {
            return back()->with('error', 'Sesi sudah selesai.');
        }

        $session->update(['status' => 'closed']);

        return back()->with('success', 'Sesi voting berhasil ditutup.');
    }

    private function checkRole(): void
    {
        if (Auth::user()->role !== 'siswa_pengelola') {
            abort(403, 'Hanya Siswa Pengelola yang dapat mengakses halaman ini.');
        }
    }
}
