<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use App\Models\Candidate;
use App\Models\Vote;
use App\Models\VotingSession;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SiswaVotingApiController extends Controller
{
    // GET /api/v1/siswa/voting
    public function index(): JsonResponse
    {
        $isActive = AppSetting::isEvotingActive();
        if (!$isActive) {
            return response()->json([
                'is_evoting_active' => false,
                'message'           => 'Fitur E-Voting saat ini sedang dinonaktifkan oleh Administrator.',
                'sessions'          => [],
            ]);
        }

        $userId   = Auth::id();
        $sessions = VotingSession::whereIn('status', ['active', 'closed'])
            ->with(['candidates', 'votes'])
            ->orderByRaw("CASE status WHEN 'active' THEN 0 ELSE 1 END")
            ->orderBy('end_time', 'desc')
            ->get()
            ->map(function (VotingSession $s) use ($userId) {
                $hasVoted = $s->hasVoted($userId);
                return [
                    'id'               => $s->id,
                    'title'            => $s->title,
                    'description'      => $s->description,
                    'status'           => $s->status,
                    'status_label'     => $s->status === 'active' ? 'Berlangsung' : 'Selesai',
                    'start_time'       => $s->start_time->toIso8601String(),
                    'end_time'         => $s->end_time->toIso8601String(),
                    'candidates_count' => $s->candidates->count(),
                    'total_votes'      => $s->votes->count(),
                    'user_has_voted'   => $hasVoted,
                ];
            });

        return response()->json([
            'is_evoting_active' => true,
            'sessions'          => $sessions,
        ]);
    }

    // GET /api/v1/siswa/voting/{id}
    public function show(int $id): JsonResponse
    {
        if (!AppSetting::isEvotingActive()) {
            return response()->json(['message' => 'Fitur E-Voting sedang dinonaktifkan.'], 403);
        }

        $session = VotingSession::whereIn('status', ['active', 'closed'])
            ->with(['candidates.votes', 'votes'])
            ->find($id);

        if (!$session) {
            return response()->json(['message' => 'Sesi E-Voting tidak ditemukan.'], 404);
        }

        $userId     = Auth::id();
        $hasVoted   = $session->hasVoted($userId);
        $userVote   = $session->myVote($userId);
        $totalVotes = $session->votes->count();

        $candidates = $session->candidates->map(function (Candidate $c) use ($totalVotes) {
            $votesCount = $c->votes->count();
            $percentage = $totalVotes > 0 ? round(($votesCount / $totalVotes) * 100, 1) : 0;
            return [
                'id'            => $c->id,
                'number'        => $c->candidate_number,
                'name'          => $c->name,
                'photo_url'     => $c->photo_url,
                'vision'        => $c->vision,
                'mission'       => $c->mission,
                'votes_count'   => $votesCount,
                'percentage'    => $percentage,
            ];
        })->sortBy('number')->values();

        return response()->json([
            'id'              => $session->id,
            'title'           => $session->title,
            'description'     => $session->description,
            'status'          => $session->status,
            'start_time'      => $session->start_time->toIso8601String(),
            'end_time'        => $session->end_time->toIso8601String(),
            'total_votes'     => $totalVotes,
            'user_has_voted'  => $hasVoted,
            'user_voted_id'   => $userVote?->candidate_id,
            'candidates'      => $candidates,
        ]);
    }

    // POST /api/v1/siswa/voting/{id}/vote
    public function vote(Request $request, int $id): JsonResponse
    {
        if (!AppSetting::isEvotingActive()) {
            return response()->json(['message' => 'Fitur E-Voting sedang dinonaktifkan.'], 403);
        }

        $session = VotingSession::find($id);
        if (!$session || !$session->isActive()) {
            return response()->json(['message' => 'Sesi voting tidak sedang berlangsung.'], 400);
        }

        $userId = Auth::id();
        if ($session->hasVoted($userId)) {
            return response()->json(['message' => 'Anda sudah memberikan suara pada sesi ini.'], 400);
        }

        $request->validate([
            'candidate_id' => 'required|exists:candidates,id',
        ]);

        $candidate = Candidate::where('voting_session_id', $session->id)->find($request->candidate_id);
        if (!$candidate) {
            return response()->json(['message' => 'Kandidat tidak valid.'], 400);
        }

        Vote::create([
            'voting_session_id' => $session->id,
            'candidate_id'      => $candidate->id,
            'user_id'           => $userId,
            'ip_address'        => $request->ip(),
        ]);

        NotificationService::send(
            $userId,
            'E-Voting Terkirim! 🗳️',
            "Terima kasih telah memberikan suara pada '{$session->title}'.",
            'success'
        );

        return response()->json([
            'success' => true,
            'message' => 'Suara Anda berhasil dikirim! Terima kasih atas partisipasi Anda.',
        ]);
    }
}
