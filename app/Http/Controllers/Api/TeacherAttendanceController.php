<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TeacherAttendance;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TeacherAttendanceController extends Controller
{
    /**
     * Absensi guru mengajar di kelas siswa pada tanggal tertentu.
     * Default: hari ini.
     */
    public function index(Request $request): JsonResponse
    {
        /** @var \App\Models\User $siswa */
        $siswa = Auth::user();

        if (! $siswa->class_id) {
            return response()->json([
                'date'       => now()->toDateString(),
                'class_name' => null,
                'records'    => [],
                'message'    => 'Siswa belum terdaftar di kelas.',
            ]);
        }

        $date = $request->get('date', now()->toDateString());

        // Validasi format tanggal
        try {
            $parsed = \Carbon\Carbon::parse($date)->toDateString();
        } catch (\Exception) {
            $parsed = now()->toDateString();
        }

        $records = TeacherAttendance::with('teacher:id,name', 'subject:id,name,code')
            ->where('class_id', $siswa->class_id)
            ->whereDate('date', $parsed)
            ->orderBy('period')
            ->get();

        $className = $siswa->schoolClass?->name;

        return response()->json([
            'date'       => $parsed,
            'class_name' => $className,
            'records'    => $records->map(fn ($r) => [
                'id'           => $r->id,
                'period'       => $r->period,
                'teacher_name' => $r->teacher?->name,
                'subject_name' => $r->subject?->name,
                'subject_code' => $r->subject?->code,
                'start_time'   => $r->start_time ? substr((string) $r->start_time, 0, 5) : null,
                'end_time'     => $r->end_time   ? substr((string) $r->end_time,   0, 5) : null,
                'status'       => $r->status,
                'status_label' => $r->statusLabel(),
                'note'         => $r->note,
            ])->values(),
        ]);
    }
}
