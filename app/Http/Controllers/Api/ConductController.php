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

        $prestasiCount    = $logs->filter(fn ($l) => $l->category?->type === 'prestasi')->count();
        $pelanggaranCount = $logs->filter(fn ($l) => $l->category?->type === 'pelanggaran')->count();

        return response()->json([
            'summary' => [
                'prestasi_count'    => $prestasiCount,
                'pelanggaran_count' => $pelanggaranCount,
            ],
            'logs' => $logs->map(fn ($log) => [
                'id'            => $log->id,
                'category_name' => $log->category->name,
                'type'          => $log->category->type,
                'context'       => $log->category->context,
                'note'          => $log->note,
                'photo_url'     => $log->photo ? Storage::disk('public')->url($log->photo) : null,
                'teacher_name'  => $log->teacher?->name,
                'date'          => $log->created_at->toDateString(),
                'created_at'    => $log->created_at->toIso8601String(),
            ])->values(),
        ]);
    }
}
