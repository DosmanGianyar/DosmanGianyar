<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ScanEvent;
use App\Models\ScanEventAttendance;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ScanEventController extends Controller
{
    public function scanner(ScanEvent $scanEvent): View
    {
        $attendances = ScanEventAttendance::with(['student.schoolClass'])
            ->where('scan_event_id', $scanEvent->id)
            ->latest('scanned_at')
            ->get();

        return view('admin.scan-event', compact('scanEvent', 'attendances'));
    }

    public function scan(Request $request, ScanEvent $scanEvent): JsonResponse
    {
        $request->validate(['identifier' => 'required|string|max:50']);

        $identifier = trim($request->identifier);

        // Ekstrak NIS dari URL biodata jika input berupa URL
        if (str_contains($identifier, '/biodata/')) {
            preg_match('#/biodata/([^/?#]+)#', $identifier, $m);
            $identifier = $m[1] ?? $identifier;
        }

        $student = User::where(function ($q) use ($identifier) {
                $q->where('nis', $identifier)->orWhere('nisn', $identifier);
                if (is_numeric($identifier)) {
                    $q->orWhere('id', (int) $identifier);
                }
            })
            ->where('role', 'like', 'siswa%')
            ->with('schoolClass')
            ->first();

        if (! $student) {
            return response()->json(['status' => 'not_found', 'message' => 'Siswa tidak ditemukan.'], 404);
        }

        $existing = ScanEventAttendance::where('scan_event_id', $scanEvent->id)
            ->where('student_id', $student->id)
            ->first();

        if ($existing) {
            return response()->json([
                'status'      => 'duplicate',
                'message'     => "{$student->name} sudah discan sebelumnya.",
                'student'     => $this->studentData($student),
                'scanned_at'  => $existing->scanned_at->format('H:i:s'),
                'total'       => ScanEventAttendance::where('scan_event_id', $scanEvent->id)->count(),
            ]);
        }

        $attendance = ScanEventAttendance::create([
            'scan_event_id' => $scanEvent->id,
            'student_id'    => $student->id,
            'scanned_at'    => now(),
            'scanned_by'    => Auth::id(),
        ]);

        $className = $student->schoolClass?->name ?? '—';

        return response()->json([
            'status'        => 'success',
            'message'       => "Berhasil! {$student->name} ({$className})",
            'attendance_id' => $attendance->id,
            'student'       => $this->studentData($student),
            'scanned_at'    => $attendance->scanned_at->format('H:i:s'),
            'total'         => ScanEventAttendance::where('scan_event_id', $scanEvent->id)->count(),
        ]);
    }

    public function list(ScanEvent $scanEvent): JsonResponse
    {
        $rows = ScanEventAttendance::with(['student.schoolClass'])
            ->where('scan_event_id', $scanEvent->id)
            ->latest('scanned_at')
            ->get()
            ->map(fn ($a) => [
                'id'         => $a->id,
                'student'    => $this->studentData($a->student),
                'scanned_at' => $a->scanned_at->format('H:i:s'),
            ]);

        return response()->json(['attendances' => $rows, 'total' => $rows->count()]);
    }

    public function destroy(ScanEvent $scanEvent, ScanEventAttendance $attendance): JsonResponse
    {
        abort_if($attendance->scan_event_id !== $scanEvent->id, 404);
        $attendance->delete();

        return response()->json([
            'message' => 'Absen dihapus.',
            'total'   => ScanEventAttendance::where('scan_event_id', $scanEvent->id)->count(),
        ]);
    }

    private function studentData(User $student): array
    {
        return [
            'id'        => $student->id,
            'name'      => $student->name,
            'nis'       => $student->nis ?? '—',
            'class'     => $student->schoolClass?->name ?? '—',
            'photo_url' => $student->photo_url,
        ];
    }
}
