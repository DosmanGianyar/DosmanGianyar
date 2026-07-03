<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ConductLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ConductController extends Controller
{
    public function index(): JsonResponse
    {
        /** @var \App\Models\User $siswa */
        $siswa = Auth::user();

        $logs = ConductLog::with('category', 'teacher')
            ->where('student_id', $siswa->id)
            ->orderByDesc('created_at')
            ->get();

        $totalPoint      = $logs->sum('point');
        $prestasiPoint   = $logs->where('point', '>', 0)->sum('point');
        $pelanggaranPoint = abs($logs->where('point', '<', 0)->sum('point'));

        return response()->json([
            'summary' => [
                'total_point'       => $totalPoint,
                'prestasi_point'    => $prestasiPoint,
                'pelanggaran_point' => $pelanggaranPoint,
            ],
            'logs' => $logs->map(fn ($log) => [
                'id'            => $log->id,
                'category_name' => $log->category->name,
                'type'          => $log->category->type,
                'context'       => $log->category->context,
                'point'         => $log->point,
                'note'          => $log->note,
                'photo_url'     => $log->photo ? Storage::url($log->photo) : null,
                'teacher_name'  => $log->teacher?->name,
                'date'          => $log->created_at->toDateString(),
                'created_at'    => $log->created_at->toIso8601String(),
            ])->values(),
        ]);
    }
}
