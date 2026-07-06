<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Extracurricular;
use App\Models\ExtracurricularAttendance;
use App\Models\ExtracurricularMember;
use App\Models\ExtracurricularSession;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ExtracurricularController extends Controller
{
    // ─── Ekstra ──────────────────────────────────────────────────────────────

    /**
     * Semua ekstra aktif + status user saat ini (member/pending/null).
     */
    public function index(Request $request): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user    = $request->user();
        $extras  = Extracurricular::active()
            ->with('pembina:id,name')
            ->withCount('activeMembers')
            ->orderBy('name')
            ->get();

        $myStatus = ExtracurricularMember::where('user_id', $user->id)
            ->pluck('status', 'extracurricular_id');
        $myRole = ExtracurricularMember::where('user_id', $user->id)
            ->where('status', 'active')
            ->pluck('role', 'extracurricular_id');

        return response()->json([
            'extracurriculars' => $extras->map(fn ($e) => [
                'id'                  => $e->id,
                'name'                => $e->name,
                'description'         => $e->description,
                'logo_url'            => $e->logo ? Storage::disk('public')->url($e->logo) : null,
                'pembina_name'        => $e->pembina?->name,
                'active_members'      => $e->active_members_count,
                'max_members'         => $e->max_members,
                'is_full'             => $e->isFull(),
                'my_status'           => $myStatus[$e->id] ?? null,
                'my_role'             => $myRole[$e->id] ?? null,
            ]),
        ]);
    }

    /**
     * Ekstra yang diikuti user (semua status: active / pending_join / pending_leave).
     */
    public function myExtras(Request $request): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $members = ExtracurricularMember::with('extracurricular.pembina:id,name')
            ->where('user_id', $user->id)
            ->get();

        return response()->json([
            'my_extracurriculars' => $members->map(fn ($m) => [
                'id'             => $m->extracurricular->id,
                'name'           => $m->extracurricular->name,
                'description'    => $m->extracurricular->description,
                'logo_url'       => $m->extracurricular->logo ? Storage::disk('public')->url($m->extracurricular->logo) : null,
                'pembina_name'   => $m->extracurricular->pembina?->name,
                'my_role'        => $m->role,
                'my_status'      => $m->status,
                'role_label'     => $m->roleLabel(),
                'status_label'   => $m->statusLabel(),
                'joined_at'      => $m->created_at->toIso8601String(),
            ]),
        ]);
    }

    /**
     * Ajukan bergabung ke ekstra.
     */
    public function join(Request $request, Extracurricular $extracurricular): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        if (!$extracurricular->is_active) {
            return response()->json(['message' => 'Ekstrakurikuler ini tidak aktif.'], 422);
        }

        $existing = ExtracurricularMember::where('extracurricular_id', $extracurricular->id)
            ->where('user_id', $user->id)
            ->first();

        if ($existing) {
            return response()->json(['message' => 'Kamu sudah terdaftar atau memiliki permintaan aktif di ekstra ini.'], 422);
        }

        if ($extracurricular->isFull()) {
            return response()->json(['message' => 'Kuota anggota ekstra ini sudah penuh.'], 422);
        }

        ExtracurricularMember::create([
            'extracurricular_id' => $extracurricular->id,
            'user_id'            => $user->id,
            'role'               => 'member',
            'status'             => 'pending_join',
        ]);

        return response()->json(['message' => 'Permintaan bergabung berhasil dikirim. Menunggu persetujuan.']);
    }

    /**
     * Ajukan keluar dari ekstra.
     */
    public function leave(Request $request, Extracurricular $extracurricular): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user   = $request->user();
        $member = ExtracurricularMember::where('extracurricular_id', $extracurricular->id)
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->first();

        if (!$member) {
            return response()->json(['message' => 'Kamu bukan anggota aktif ekstra ini.'], 422);
        }

        $member->update(['status' => 'pending_leave']);

        return response()->json(['message' => 'Permintaan keluar berhasil dikirim. Menunggu persetujuan.']);
    }

    // ─── Sesi ────────────────────────────────────────────────────────────────

    /**
     * List sesi dari semua ekstra yang diikuti user aktif.
     */
    public function sessions(Request $request): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $myExtraIds = ExtracurricularMember::where('user_id', $user->id)
            ->where('status', 'active')
            ->pluck('extracurricular_id');

        $filter    = $request->query('filter', 'upcoming'); // upcoming | past
        $sessQuery = ExtracurricularSession::with('extracurricular:id,name')
            ->whereIn('extracurricular_id', $myExtraIds);

        $sessions = $filter === 'past'
            ? $sessQuery->past()->limit(30)->get()
            : $sessQuery->upcoming()->limit(20)->get();

        return response()->json([
            'sessions' => $sessions->map(fn ($s) => $this->sessionResource($s, $user->id)),
        ]);
    }

    /**
     * Detail sesi + daftar anggota + status absen mereka.
     */
    public function sessionDetail(Request $request, ExtracurricularSession $session): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $session->load('extracurricular.pembina:id,name', 'creator:id,name');

        // Cek akses: harus anggota aktif
        $myMember = ExtracurricularMember::where('extracurricular_id', $session->extracurricular_id)
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->first();

        if (!$myMember) {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        // Anggota aktif + status absen
        $members = ExtracurricularMember::with('user:id,name,nis,class_id')
            ->where('extracurricular_id', $session->extracurricular_id)
            ->where('status', 'active')
            ->get();

        $attendances = ExtracurricularAttendance::where('session_id', $session->id)
            ->pluck('status', 'user_id');

        return response()->json([
            'session'   => $this->sessionResource($session, $user->id),
            'my_role'   => $myMember->role,
            'members'   => $members->map(fn ($m) => [
                'user_id'    => $m->user_id,
                'name'       => $m->user?->name,
                'nis'        => $m->user?->nis,
                'role'       => $m->role,
                'attendance' => $attendances[$m->user_id] ?? null,
            ]),
        ]);
    }

    /**
     * Buat sesi baru (ketua/pembina saja).
     */
    public function createSession(Request $request): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $data = $request->validate([
            'extracurricular_id' => 'required|exists:extracurriculars,id',
            'title'              => 'required|string|max:120',
            'session_date'       => 'required|date',
            'start_time'         => 'required|date_format:H:i',
            'end_time'           => 'required|date_format:H:i|after:start_time',
            'location'           => 'nullable|string|max:100',
            'notes'              => 'nullable|string',
        ]);

        $this->requireKetuaOrPembina($user, (int) $data['extracurricular_id']);

        $session = ExtracurricularSession::create(array_merge($data, [
            'created_by' => $user->id,
            'is_open'    => false,
        ]));

        return response()->json([
            'message' => 'Sesi berhasil dibuat.',
            'session' => $this->sessionResource($session->fresh(), $user->id),
        ], 201);
    }

    /**
     * Buka / tutup absen sesi.
     */
    public function toggleOpen(Request $request, ExtracurricularSession $session): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
        $this->requireKetuaOrPembina($user, $session->extracurricular_id);

        $session->update(['is_open' => !$session->is_open]);

        return response()->json([
            'message' => $session->is_open ? 'Absen dibuka.' : 'Absen ditutup.',
            'is_open' => $session->is_open,
        ]);
    }

    /**
     * Centang / update kehadiran per anggota.
     * Body: [{ user_id: int, status: 'hadir'|'alpa' }, ...]
     */
    public function markAttendance(Request $request, ExtracurricularSession $session): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
        $this->requireKetuaOrPembina($user, $session->extracurricular_id);

        $data = $request->validate([
            'attendances'             => 'required|array|min:1',
            'attendances.*.user_id'   => 'required|integer|exists:users,id',
            'attendances.*.status'    => 'required|in:hadir,alpa',
        ]);

        foreach ($data['attendances'] as $item) {
            ExtracurricularAttendance::updateOrCreate(
                ['session_id' => $session->id, 'user_id' => $item['user_id']],
                ['status' => $item['status'], 'marked_by' => $user->id, 'marked_at' => now()]
            );
        }

        return response()->json(['message' => 'Kehadiran berhasil dicatat.']);
    }

    // ─── Private helpers ─────────────────────────────────────────────────────

    private function sessionResource(ExtracurricularSession $s, int $userId): array
    {
        return [
            'id'                   => $s->id,
            'extracurricular_id'   => $s->extracurricular_id,
            'extracurricular_name' => $s->extracurricular?->name,
            'title'                => $s->title,
            'session_date'         => $s->session_date->toDateString(),
            'start_time'           => substr($s->start_time, 0, 5),
            'end_time'             => substr($s->end_time, 0, 5),
            'location'             => $s->location,
            'notes'                => $s->notes,
            'is_open'              => $s->is_open,
            'hadir_count'          => $s->hadirCount(),
            'alpa_count'           => $s->alpaCount(),
        ];
    }

    private function requireKetuaOrPembina(\App\Models\User $user, int $extraId): void
    {
        $extra = Extracurricular::findOrFail($extraId);

        $isKetua = ExtracurricularMember::where('extracurricular_id', $extraId)
            ->where('user_id', $user->id)
            ->where('role', 'ketua')
            ->where('status', 'active')
            ->exists();

        $isPembina = $extra->pembina_id === $user->id;
        $isAdmin   = $user->role === 'admin';

        if (!$isKetua && !$isPembina && !$isAdmin) {
            abort(403, 'Hanya ketua atau pembina yang dapat melakukan aksi ini.');
        }
    }
}
