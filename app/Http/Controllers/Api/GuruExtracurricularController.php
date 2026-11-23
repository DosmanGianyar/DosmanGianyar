<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Extracurricular;
use App\Models\ExtracurricularAttendance;
use App\Models\ExtracurricularMember;
use App\Models\ExtracurricularSession;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class GuruExtracurricularController extends Controller
{
    /**
     * Daftar ekstrakurikuler yang dibina oleh guru yang login (atau semua jika admin/kesiswaan).
     */
    public function index(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        if (! $this->isAuthorizedRole($user)) {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        $query = Extracurricular::active()
            ->with(['pembina:id,name', 'teachers:id,name'])
            ->withCount(['pendingMembers', 'sessions as journals_count']);

        if (! $this->isAdminRole($user)) {
            $query->where(function ($q) use ($user) {
                $q->where('pembina_id', $user->id)
                    ->orWhereHas('teachers', fn ($t) => $t->where('users.id', $user->id));
            });
        }

        $extras = $query->orderBy('name')->get();

        $lastDates = ExtracurricularSession::whereIn('extracurricular_id', $extras->pluck('id'))
            ->selectRaw('extracurricular_id, MAX(session_date) as last_date')
            ->groupBy('extracurricular_id')
            ->pluck('last_date', 'extracurricular_id');

        $items = $extras->map(function (Extracurricular $extra) use ($lastDates) {
            $activeMembers = $this->getActiveMembersCollection($extra);
            $lastDate = $lastDates[$extra->id] ?? null;

            return [
                'id'                    => $extra->id,
                'name'                  => $extra->name,
                'description'           => $extra->description,
                'contact_person'        => $extra->contact_person,
                'logo_url'              => $extra->logo ? Storage::disk('public')->url($extra->logo) : null,
                'pembina_names'         => $extra->pembina_names,
                'active_members_count'  => $activeMembers->count(),
                'pending_members_count' => (int) ($extra->pending_members_count ?? 0),
                'journals_count'        => (int) ($extra->journals_count ?? 0),
                'last_session_date'     => $lastDate ? Carbon::parse($lastDate)->toDateString() : null,
            ];
        })->values();

        return response()->json([
            'extracurriculars' => $items,
        ]);
    }

    /**
     * Daftar seluruh siswa aktif anggota ekstrakurikuler + statistik kehadiran.
     */
    public function members(Request $request, Extracurricular $extracurricular): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        if (! $this->canManageExtra($user, $extracurricular)) {
            return response()->json(['message' => 'Anda bukan pembina ekstrakurikuler ini.'], 403);
        }

        $members = $this->getActiveMembersCollection($extracurricular);

        $sessionIds = ExtracurricularSession::where('extracurricular_id', $extracurricular->id)
            ->pluck('id');
        $totalSessions = $sessionIds->count();

        $attByUser = $sessionIds->isEmpty()
            ? collect()
            : ExtracurricularAttendance::whereIn('session_id', $sessionIds)
                ->get()
                ->groupBy('user_id');

        return response()->json([
            'extracurricular' => [
                'id'            => $extracurricular->id,
                'name'          => $extracurricular->name,
                'pembina_names' => $extracurricular->pembina_names,
                'members_count' => $members->count(),
            ],
            'members' => $members->map(function (array $m) use ($attByUser, $totalSessions) {
                /** @var Collection $userAtts */
                $userAtts = $attByUser->get($m['user_id'], collect());

                return [
                    'user_id'        => $m['user_id'],
                    'name'           => $m['name'],
                    'nis'            => $m['nis'],
                    'class_name'     => $m['class_name'],
                    'role'           => $m['role'],
                    'role_label'     => $m['role_label'],
                    'joined_at'      => $m['joined_at'],
                    'hadir_count'    => $userAtts->where('status', 'hadir')->count(),
                    'izin_count'     => $userAtts->where('status', 'izin')->count(),
                    'sakit_count'    => $userAtts->where('status', 'sakit')->count(),
                    'alpa_count'     => $userAtts->where('status', 'alpa')->count(),
                    'total_sessions' => $totalSessions,
                ];
            })->values(),
        ]);
    }

    /**
     * Daftar riwayat jurnal & sesi ekstrakurikuler.
     */
    public function journals(Request $request, Extracurricular $extracurricular): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        if (! $this->canManageExtra($user, $extracurricular)) {
            return response()->json(['message' => 'Anda bukan pembina ekstrakurikuler ini.'], 403);
        }

        $totalMembers = $this->getActiveMembersCollection($extracurricular)->count();

        $query = ExtracurricularSession::where('extracurricular_id', $extracurricular->id)
            ->with(['extracurricular:id,name', 'creator:id,name', 'attendances']);

        if ($request->filled('month') && $request->filled('year')) {
            $query->whereMonth('session_date', (int) $request->month)
                ->whereYear('session_date', (int) $request->year);
        }

        $sessions = $query->orderByDesc('session_date')
            ->orderByDesc('start_time')
            ->orderByDesc('id')
            ->paginate(20);

        return response()->json([
            'data' => $sessions->getCollection()
                ->map(fn (ExtracurricularSession $s) => $this->formatJournalSummary($s, $totalMembers))
                ->values(),
            'meta' => [
                'current_page' => $sessions->currentPage(),
                'last_page'    => $sessions->lastPage(),
                'total'        => $sessions->total(),
            ],
        ]);
    }

    /**
     * Detail lengkap 1 jurnal + daftar anggota beserta status kehadiran pada sesi tersebut.
     */
    public function showJournal(Request $request, ExtracurricularSession $session): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $session->load([
            'extracurricular.teachers',
            'extracurricular.pembina',
            'creator:id,name',
            'updater:id,name',
            'attendances.user.schoolClass:id,name',
        ]);

        $extra = $session->extracurricular;
        if (! $extra || ! $this->canManageExtra($user, $extra)) {
            return response()->json(['message' => 'Anda bukan pembina ekstrakurikuler ini.'], 403);
        }

        $activeMembers = $this->getActiveMembersCollection($extra);
        $attByUser = $session->attendances->keyBy('user_id');

        $memberRows = $activeMembers->map(function (array $m) use ($attByUser) {
            $att = $attByUser->get($m['user_id']);

            return [
                'user_id'    => $m['user_id'],
                'name'       => $m['name'],
                'nis'        => $m['nis'],
                'class_name' => $m['class_name'],
                'role'       => $m['role'],
                'role_label' => $m['role_label'],
                'attendance' => $att?->status,
                'note'       => $att?->note,
            ];
        })->values();

        // Sertakan siswa yang pernah tercatat absensinya di sesi ini meski status keanggotaannya berubah
        $activeIds = $activeMembers->pluck('user_id')->all();
        foreach ($session->attendances as $att) {
            if (! in_array($att->user_id, $activeIds, true) && $att->user) {
                $memberRows->push([
                    'user_id'    => $att->user_id,
                    'name'       => $att->user->name,
                    'nis'        => $att->user->nis,
                    'class_name' => $att->user->schoolClass?->name ?? '—',
                    'role'       => 'member',
                    'role_label' => 'Anggota',
                    'attendance' => $att->status,
                    'note'       => $att->note,
                ]);
            }
        }

        return response()->json([
            'journal' => $this->formatJournalSummary($session, $activeMembers->count()),
            'members' => $memberRows->values(),
        ]);
    }

    /**
     * Buat jurnal + catat absensi anggota sekaligus.
     */
    public function storeJournal(Request $request, Extracurricular $extracurricular): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        if (! $this->canManageExtra($user, $extracurricular)) {
            return response()->json(['message' => 'Anda bukan pembina ekstrakurikuler ini.'], 403);
        }

        $this->decodeAttendancesJsonIfNeeded($request);

        $data = $request->validate([
            'title'                  => 'required|string|max:150',
            'session_date'           => 'required|date',
            'start_time'             => 'required|date_format:H:i',
            'end_time'               => 'required|date_format:H:i|after:start_time',
            'location'               => 'nullable|string|max:120',
            'material'               => 'required|string|max:1500',
            'activity'               => 'required|string|max:2000',
            'obstacles'              => 'nullable|string|max:1000',
            'notes'                  => 'nullable|string|max:1000',
            'photo'                  => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'attendances'            => 'nullable|array',
            'attendances.*.user_id'  => 'required|integer|exists:users,id',
            'attendances.*.status'   => 'required|string|in:hadir,izin,sakit,alpa',
            'attendances.*.note'     => 'nullable|string|max:255',
        ]);

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = Storage::disk('public')->putFile('extracurricular-journals', $request->file('photo'));
        }

        try {
            $session = DB::transaction(function () use ($data, $extracurricular, $user, $photoPath) {
                $created = ExtracurricularSession::create([
                    'extracurricular_id' => $extracurricular->id,
                    'title'              => $data['title'],
                    'session_date'       => $data['session_date'],
                    'start_time'         => $data['start_time'],
                    'end_time'           => $data['end_time'],
                    'location'           => $data['location'] ?? null,
                    'material'           => $data['material'],
                    'activity'           => $data['activity'],
                    'obstacles'          => $data['obstacles'] ?? null,
                    'notes'              => $data['notes'] ?? null,
                    'photo'              => $photoPath,
                    'created_by'         => $user->id,
                    'updated_by'         => $user->id,
                    'is_open'            => false,
                ]);

                $this->upsertAttendances($created->id, $data['attendances'] ?? [], $user->id);

                return $created;
            });
        } catch (\Throwable $e) {
            if ($photoPath) {
                Storage::disk('public')->delete($photoPath);
            }
            return response()->json(['message' => 'Gagal menyimpan jurnal ekstra: ' . $e->getMessage()], 500);
        }

        $session->load(['extracurricular:id,name', 'creator:id,name', 'attendances']);
        $totalMembers = $this->getActiveMembersCollection($extracurricular)->count();

        return response()->json([
            'message' => 'Jurnal & absensi ekstrakurikuler berhasil disimpan.',
            'journal' => $this->formatJournalSummary($session, $totalMembers),
        ], 201);
    }

    /**
     * Perbarui isi jurnal (dan opsional foto + daftar kehadiran).
     */
    public function updateJournal(Request $request, ExtracurricularSession $session): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $session->load(['extracurricular.teachers', 'extracurricular.pembina']);
        $extra = $session->extracurricular;

        if (! $extra || ! $this->canManageExtra($user, $extra)) {
            return response()->json(['message' => 'Anda bukan pembina ekstrakurikuler ini.'], 403);
        }

        $this->decodeAttendancesJsonIfNeeded($request);

        $data = $request->validate([
            'title'                  => 'sometimes|required|string|max:150',
            'session_date'           => 'sometimes|required|date',
            'start_time'             => 'sometimes|required|date_format:H:i',
            'end_time'               => 'sometimes|required|date_format:H:i',
            'location'               => 'nullable|string|max:120',
            'material'               => 'sometimes|required|string|max:1500',
            'activity'               => 'sometimes|required|string|max:2000',
            'obstacles'              => 'nullable|string|max:1000',
            'notes'                  => 'nullable|string|max:1000',
            'remove_photo'           => 'nullable|boolean',
            'photo'                  => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'attendances'            => 'nullable|array',
            'attendances.*.user_id'  => 'required|integer|exists:users,id',
            'attendances.*.status'   => 'required|string|in:hadir,izin,sakit,alpa',
            'attendances.*.note'     => 'nullable|string|max:255',
        ]);

        $effectiveStart = $data['start_time'] ?? substr((string) $session->start_time, 0, 5);
        $effectiveEnd   = $data['end_time'] ?? substr((string) $session->end_time, 0, 5);
        if ($effectiveEnd <= $effectiveStart) {
            return response()->json([
                'message' => 'Jam selesai harus lebih akhir dari jam mulai.',
                'errors'  => ['end_time' => ['Jam selesai harus lebih akhir dari jam mulai.']],
            ], 422);
        }

        $oldPhoto = $session->photo;
        $newPhotoPath = null;
        if ($request->hasFile('photo')) {
            $newPhotoPath = Storage::disk('public')->putFile('extracurricular-journals', $request->file('photo'));
        }

        try {
            DB::transaction(function () use ($request, $session, $data, $user, $newPhotoPath) {
                $updates = [];
                foreach (['title', 'session_date', 'start_time', 'end_time', 'location', 'material', 'activity', 'obstacles', 'notes'] as $field) {
                    if ($request->exists($field)) {
                        $updates[$field] = $data[$field] ?? null;
                    }
                }

                if ($newPhotoPath !== null) {
                    $updates['photo'] = $newPhotoPath;
                } elseif ($request->boolean('remove_photo')) {
                    $updates['photo'] = null;
                }

                $updates['updated_by'] = $user->id;
                $session->update($updates);

                if ($request->has('attendances')) {
                    $this->upsertAttendances($session->id, $data['attendances'] ?? [], $user->id);
                }
            });

            if ($oldPhoto && ($newPhotoPath !== null || $request->boolean('remove_photo'))) {
                Storage::disk('public')->delete($oldPhoto);
            }
        } catch (\Throwable $e) {
            if ($newPhotoPath) {
                Storage::disk('public')->delete($newPhotoPath);
            }
            return response()->json(['message' => 'Gagal memperbarui jurnal: ' . $e->getMessage()], 500);
        }

        $session->refresh()->load(['extracurricular:id,name', 'creator:id,name', 'attendances']);
        $totalMembers = $this->getActiveMembersCollection($extra)->count();

        return response()->json([
            'message' => 'Jurnal ekstrakurikuler berhasil diperbarui.',
            'journal' => $this->formatJournalSummary($session, $totalMembers),
        ]);
    }

    /**
     * Simpan atau perbarui absensi anggota pada suatu sesi/jurnal.
     */
    public function saveAttendance(Request $request, ExtracurricularSession $session): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $session->load(['extracurricular.teachers', 'extracurricular.pembina']);
        $extra = $session->extracurricular;

        if (! $extra || ! $this->canManageExtra($user, $extra)) {
            return response()->json(['message' => 'Anda bukan pembina ekstrakurikuler ini.'], 403);
        }

        $data = $request->validate([
            'attendances'            => 'required|array|min:1',
            'attendances.*.user_id'  => 'required|integer|exists:users,id',
            'attendances.*.status'   => 'required|string|in:hadir,izin,sakit,alpa',
            'attendances.*.note'     => 'nullable|string|max:255',
        ]);

        DB::transaction(function () use ($session, $data, $user) {
            $this->upsertAttendances($session->id, $data['attendances'], $user->id);
        });

        $session->refresh()->load(['extracurricular:id,name', 'creator:id,name', 'attendances']);
        $totalMembers = $this->getActiveMembersCollection($extra)->count();

        return response()->json([
            'message' => 'Absensi anggota ekstrakurikuler berhasil disimpan.',
            'journal' => $this->formatJournalSummary($session, $totalMembers),
        ]);
    }

    /**
     * Hapus jurnal + absensi + foto terkait.
     */
    public function destroyJournal(Request $request, ExtracurricularSession $session): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $session->load(['extracurricular.teachers', 'extracurricular.pembina']);
        $extra = $session->extracurricular;

        if (! $extra || ! $this->canManageExtra($user, $extra)) {
            return response()->json(['message' => 'Anda bukan pembina ekstrakurikuler ini.'], 403);
        }

        $photoPath = $session->photo;

        DB::transaction(function () use ($session) {
            $session->attendances()->delete();
            $session->delete();
        });

        if ($photoPath) {
            Storage::disk('public')->delete($photoPath);
        }

        return response()->json([
            'message' => 'Jurnal ekstrakurikuler berhasil dihapus.',
        ]);
    }

    // ─── Private Helpers ──────────────────────────────────────────────────────

    private function isAuthorizedRole(User $user): bool
    {
        return in_array($user->role, ['guru', 'admin', 'admin_kesiswaan'], true);
    }

    private function isAdminRole(User $user): bool
    {
        return in_array($user->role, ['admin', 'admin_kesiswaan'], true);
    }

    private function canManageExtra(User $user, Extracurricular $extra): bool
    {
        if (! $this->isAuthorizedRole($user)) {
            return false;
        }
        if ($this->isAdminRole($user)) {
            return true;
        }
        $extra->loadMissing('teachers');

        return $extra->isTeacherPembina($user->id);
    }

    /**
     * Gabungkan anggota aktif dari `extracurricular_members` dan `extracurricular_students`
     * tanpa duplikat, diurutkan menurut peran lalu nama siswa.
     *
     * @return Collection<int, array<string, mixed>>
     */
    private function getActiveMembersCollection(Extracurricular $extra): Collection
    {
        $rolePriority = fn (string $role): int => match ($role) {
            'ketua'       => 1,
            'wakil_ketua' => 2,
            default       => 3,
        };

        $roleLabel = fn (string $role): string => match ($role) {
            'ketua'       => 'Ketua',
            'wakil_ketua' => 'Wakil Ketua',
            default       => 'Anggota',
        };

        $map = [];

        $memberRows = ExtracurricularMember::with(['user:id,name,nis,class_id', 'user.schoolClass:id,name'])
            ->where('extracurricular_id', $extra->id)
            ->where('status', 'active')
            ->get();

        foreach ($memberRows as $m) {
            if (! $m->user) {
                continue;
            }
            $role = $m->role === 'anggota' ? 'member' : ($m->role ?: 'member');
            $map[$m->user_id] = [
                'user_id'    => (int) $m->user_id,
                'name'       => $m->user->name,
                'nis'        => $m->user->nis,
                'class_name' => $m->user->schoolClass?->name ?? '—',
                'role'       => $role,
                'role_label' => $roleLabel($role),
                'joined_at'  => $m->approved_at?->toIso8601String() ?? $m->created_at?->toIso8601String(),
            ];
        }

        if (DB::getSchemaBuilder()->hasTable('extracurricular_students')) {
            $pivotRows = DB::table('extracurricular_students')
                ->where('extracurricular_id', $extra->id)
                ->get(['student_id', 'role', 'created_at']);

            $missingIds = $pivotRows
                ->pluck('student_id')
                ->map(fn ($id) => (int) $id)
                ->filter(fn (int $id) => ! isset($map[$id]))
                ->unique()
                ->values();

            $missingUsers = $missingIds->isEmpty()
                ? collect()
                : User::with('schoolClass:id,name')
                    ->whereIn('id', $missingIds)
                    ->get(['id', 'name', 'nis', 'class_id'])
                    ->keyBy('id');

            foreach ($pivotRows as $p) {
                $uid = (int) $p->student_id;
                $pivotRole = ($p->role === 'anggota' || empty($p->role)) ? 'member' : $p->role;

                if (isset($map[$uid])) {
                    if ($rolePriority($pivotRole) < $rolePriority($map[$uid]['role'])) {
                        $map[$uid]['role'] = $pivotRole;
                        $map[$uid]['role_label'] = $roleLabel($pivotRole);
                    }
                    continue;
                }

                $student = $missingUsers->get($uid);
                if (! $student) {
                    continue;
                }

                $map[$uid] = [
                    'user_id'    => $uid,
                    'name'       => $student->name,
                    'nis'        => $student->nis,
                    'class_name' => $student->schoolClass?->name ?? '—',
                    'role'       => $pivotRole,
                    'role_label' => $roleLabel($pivotRole),
                    'joined_at'  => $p->created_at ? Carbon::parse($p->created_at)->toIso8601String() : null,
                ];
            }
        }

        return collect($map)
            ->sort(function (array $a, array $b) use ($rolePriority) {
                $prioCmp = $rolePriority($a['role']) <=> $rolePriority($b['role']);
                if ($prioCmp !== 0) {
                    return $prioCmp;
                }
                return strcasecmp($a['name'], $b['name']);
            })
            ->values();
    }

    private function formatJournalSummary(ExtracurricularSession $session, int $totalMembers): array
    {
        $atts = $session->relationLoaded('attendances')
            ? $session->attendances
            : $session->attendances()->get();

        return [
            'id'                   => $session->id,
            'extracurricular_id'   => $session->extracurricular_id,
            'extracurricular_name' => $session->extracurricular?->name ?? '—',
            'title'                => $session->title,
            'session_date'         => $session->session_date?->toDateString(),
            'date_formatted'       => $session->session_date
                ? $session->session_date->copy()->locale('id')->translatedFormat('l, d M Y')
                : '—',
            'start_time'           => substr((string) $session->start_time, 0, 5),
            'end_time'             => substr((string) $session->end_time, 0, 5),
            'location'             => $session->location,
            'material'             => $session->material,
            'activity'             => $session->activity,
            'obstacles'            => $session->obstacles,
            'notes'                => $session->notes,
            'photo_url'            => $session->photo ? Storage::disk('public')->url($session->photo) : null,
            'creator_name'         => $session->creator?->name,
            'is_open'              => (bool) $session->is_open,
            'has_journal'          => $session->hasJournal(),
            'hadir_count'          => $atts->where('status', 'hadir')->count(),
            'izin_count'           => $atts->where('status', 'izin')->count(),
            'sakit_count'          => $atts->where('status', 'sakit')->count(),
            'alpa_count'           => $atts->where('status', 'alpa')->count(),
            'total_marked'         => $atts->count(),
            'total_members'        => $totalMembers,
        ];
    }

    private function decodeAttendancesJsonIfNeeded(Request $request): void
    {
        if ($request->has('attendances') && is_string($request->input('attendances'))) {
            $decoded = json_decode($request->input('attendances'), true);
            if (is_array($decoded)) {
                $request->merge(['attendances' => $decoded]);
            }
        }
    }

    /**
     * @param array<int, array<string, mixed>> $items
     */
    private function upsertAttendances(int $sessionId, array $items, int $markedBy): void
    {
        foreach ($items as $item) {
            ExtracurricularAttendance::updateOrCreate(
                [
                    'session_id' => $sessionId,
                    'user_id'    => (int) $item['user_id'],
                ],
                [
                    'status'    => $item['status'],
                    'note'      => $item['note'] ?? null,
                    'marked_by' => $markedBy,
                    'marked_at' => now(),
                ]
            );
        }
    }
}
