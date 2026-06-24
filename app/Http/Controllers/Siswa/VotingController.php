<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\VotingSession;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class VotingController extends Controller
{
    public function index(): View
    {
        $sessions = VotingSession::whereIn('status', ['active', 'closed'])
            ->with(['candidates', 'votes'])
            ->orderByRaw("CASE status WHEN 'active' THEN 0 ELSE 1 END")
            ->orderBy('end_time', 'desc')
            ->get()
            ->map(function (VotingSession $session) {
                $session->user_has_voted = $session->hasVoted(Auth::id());
                $session->user_vote      = $session->myVote(Auth::id());
                return $session;
            });

        return view('siswa.voting.index', compact('sessions'));
    }

    public function show(VotingSession $session): View|RedirectResponse
    {
        if ($session->isDraft()) {
            abort(404);
        }

        $session->load(['candidates.votes', 'votes']);
        $hasVoted  = $session->hasVoted(Auth::id());
        $userVote  = $session->myVote(Auth::id());
        $totalVotes = $session->votes()->count();

        if ($hasVoted || $session->isClosed()) {
            return view('siswa.voting.results', compact('session', 'hasVoted', 'userVote', 'totalVotes'));
        }

        return view('siswa.voting.show', compact('session'));
    }

    public function vote(Request $request, VotingSession $session): RedirectResponse
    {
        if (! $session->isActive()) {
            return back()->with('error', 'Sesi voting tidak sedang berlangsung.');
        }

        if ($session->hasVoted(Auth::id())) {
            return back()->with('error', 'Kamu sudah memberikan suara.');
        }

        $request->validate([
            'candidate_id' => 'required|exists:candidates,id',
        ]);

        $candidate = $session->candidates()->findOrFail($request->candidate_id);

        $session->votes()->create([
            'voter_id'     => Auth::id(),
            'candidate_id' => $candidate->id,
        ]);

        return redirect()->route('siswa.voting.show', $session)
            ->with('success', "Suaramu untuk {$candidate->name} berhasil dicatat. Terima kasih!");
    }
}
