<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ExtracurricularAttendance;
use App\Models\ExtracurricularMember;
use App\Models\ExtracurricularSession;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

class ExtracurricularExportController extends Controller
{
    public function pdf(ExtracurricularSession $session): Response
    {
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
}
