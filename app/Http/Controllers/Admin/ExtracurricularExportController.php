<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Extracurricular;
use App\Models\ExtracurricularAttendance;
use App\Models\ExtracurricularMember;
use App\Models\ExtracurricularSession;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Illuminate\Http\Request;

class ExtracurricularExportController extends Controller
{
    public function pdf(ExtracurricularSession $session): Response
    {
        @ini_set('memory_limit', '512M');
        @set_time_limit(300);

        $session->load('extracurricular');

        $activeMembers = ExtracurricularMember::with('user.schoolClass')
            ->where('extracurricular_id', $session->extracurricular_id)
            ->where('status', 'active')
            ->orderBy('user_id')
            ->get();

        $attendances = ExtracurricularAttendance::where('session_id', $session->id)
            ->pluck('status', 'user_id');

        $rows = $activeMembers->map(function (ExtracurricularMember $m) use ($attendances) {
            $m->attendance_status = $attendances[$m->user_id] ?? 'alpa';
            return $m;
        });

        $hadirCount = $rows->where('attendance_status', 'hadir')->count();
        $alpaCount  = $rows->where('attendance_status', 'alpa')->count();

        $pdf = Pdf::loadView('exports.extracurricular_attendance_pdf', [
            'session'    => $session,
            'rows'       => $rows,
            'hadirCount' => $hadirCount,
            'alpaCount'  => $alpaCount,
            'totalCount' => $rows->count(),
        ])->setPaper('a4', 'portrait');

        $filename = 'rekap-ekstra-' . $session->extracurricular->name . '-' . $session->session_date->format('Ymd') . '.pdf';

        return $pdf->download($filename);
    }

    /**
     * Cetak daftar anggota aktif suatu ekstrakurikuler.
     */
    public function membersPdf(Extracurricular $extracurricular): Response
    {
        @ini_set('memory_limit', '512M');
        @set_time_limit(300);

        $members = ExtracurricularMember::with('user.schoolClass')
            ->where('extracurricular_id', $extracurricular->id)
            ->where('status', 'active')
            ->orderBy('role', 'asc')  // ketua dulu
            ->get()
            ->sortByDesc(fn ($m) => $m->role === 'ketua' ? 1 : 0);

        $pdf = Pdf::loadView('exports.extracurricular_members_pdf', [
            'extracurricular' => $extracurricular,
            'members'         => $members,
        ])->setPaper('a4', 'portrait');

        $filename = 'anggota-' . str($extracurricular->name)->slug() . '.pdf';

        return $pdf->download($filename);
    }

    /**
     * Cetak daftar siswa yang tidak memiliki ekstrakurikuler.
     */
    public function noEkstraPdf(Request $request): Response
    {
        @ini_set('memory_limit', '512M');
        @set_time_limit(300);

        $query = User::where('role', 'siswa')
            ->whereDoesntHave('memberExtracurriculars', fn ($q) => $q->where('status', 'active'))
            ->with('schoolClass')
            ->orderBy('name');

        if ($request->filled('class_id')) {
            $query->where('class_id', $request->class_id);
        }

        $students  = $query->get();
        $className = $request->filled('class_name') ? $request->class_name : null;

        $pdf = Pdf::loadView('exports.students_without_extracurricular_pdf', [
            'students'  => $students,
            'className' => $className,
        ])->setPaper('a4', 'portrait');

        return $pdf->download('siswa-tanpa-ekstra.pdf');
    }
}
