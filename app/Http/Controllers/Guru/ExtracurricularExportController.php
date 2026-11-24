<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Extracurricular;
use App\Models\ExtracurricularMember;
use App\Models\SchoolClass;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ExtracurricularExportController extends Controller
{
    public function membersPdf(Extracurricular $extracurricular, Request $request): Response
    {
        /** @var User $user */
        $user = $request->user();
        $extracurricular->loadMissing(['teachers', 'pembina']);

        $isPembina = $extracurricular->isTeacherPembina($user->id);
        $isAdmin = in_array($user->role, ['admin', 'admin_kesiswaan'], true);

        if (! $isPembina && ! $isAdmin) {
            abort(403, 'Anda bukan pembina ekstrakurikuler ini.');
        }

        return $this->renderMembersPdf($extracurricular, $request);
    }

    public function membersPdfSigned(Extracurricular $extracurricular, Request $request): Response
    {
        $extracurricular->loadMissing(['teachers', 'pembina']);

        return $this->renderMembersPdf($extracurricular, $request);
    }

    private function renderMembersPdf(Extracurricular $extracurricular, Request $request): Response
    {
        @ini_set('memory_limit', '512M');
        @set_time_limit(300);

        $rolePriority = fn (string $role): int => match ($role) {
            'ketua'       => 1,
            'wakil_ketua' => 2,
            default       => 3,
        };

        $map = [];

        $memberRows = ExtracurricularMember::with(['user.schoolClass'])
            ->where('extracurricular_id', $extracurricular->id)
            ->where('status', 'active')
            ->get();

        foreach ($memberRows as $m) {
            if (! $m->user) {
                continue;
            }
            $m->role = ($m->role === 'anggota' || empty($m->role)) ? 'member' : $m->role;
            $map[$m->user_id] = $m;
        }

        if (Schema::hasTable('extracurricular_students')) {
            $pivotRows = DB::table('extracurricular_students')
                ->where('extracurricular_id', $extracurricular->id)
                ->get(['student_id', 'role', 'created_at']);

            $missingIds = $pivotRows
                ->pluck('student_id')
                ->map(fn ($id) => (int) $id)
                ->filter(fn (int $id) => ! isset($map[$id]))
                ->unique()
                ->values();

            $missingUsers = $missingIds->isEmpty()
                ? collect()
                : User::with('schoolClass')
                    ->whereIn('id', $missingIds)
                    ->get()
                    ->keyBy('id');

            foreach ($pivotRows as $p) {
                $uid = (int) $p->student_id;
                $pivotRole = ($p->role === 'anggota' || empty($p->role)) ? 'member' : $p->role;

                if (isset($map[$uid])) {
                    if ($rolePriority($pivotRole) < $rolePriority($map[$uid]->role)) {
                        $map[$uid]->role = $pivotRole;
                    }
                    continue;
                }

                $student = $missingUsers->get($uid);
                if (! $student) {
                    continue;
                }

                $virtual = new ExtracurricularMember([
                    'extracurricular_id' => $extracurricular->id,
                    'user_id'            => $uid,
                    'role'               => $pivotRole,
                    'status'             => 'active',
                ]);
                $virtual->created_at = $p->created_at ? Carbon::parse($p->created_at) : null;
                $virtual->setRelation('user', $student);
                $map[$uid] = $virtual;
            }
        }

        $members = collect($map);

        if ($request->filled('grade')) {
            $grade = (string) $request->query('grade');
            $members = $members->filter(
                fn (ExtracurricularMember $m) => (string) ($m->user?->schoolClass?->grade ?? '') === $grade
            );
        }

        if ($request->filled('class_id')) {
            $classId = (int) $request->query('class_id');
            $members = $members->filter(
                fn (ExtracurricularMember $m) => (int) ($m->user?->class_id ?? 0) === $classId
            );
        }

        $members = $members->sort(function (ExtracurricularMember $a, ExtracurricularMember $b) use ($rolePriority) {
            $prioCmp = $rolePriority((string) $a->role) <=> $rolePriority((string) $b->role);
            if ($prioCmp !== 0) {
                return $prioCmp;
            }
            return strcasecmp((string) ($a->user?->name ?? ''), (string) ($b->user?->name ?? ''));
        })->values();

        $filterGrade = $request->query('grade');
        $filterClass = $request->filled('class_id')
            ? SchoolClass::find($request->query('class_id'))?->name
            : null;

        $pdf = Pdf::loadView('exports.extracurricular_members_pdf', [
            'extracurricular' => $extracurricular,
            'members'         => $members,
            'filterGrade'     => $filterGrade,
            'filterClass'     => $filterClass,
        ])->setPaper('a4', 'portrait');

        $filename = 'anggota-' . str($extracurricular->name)->slug() . '.pdf';

        return $pdf->download($filename);
    }
}
