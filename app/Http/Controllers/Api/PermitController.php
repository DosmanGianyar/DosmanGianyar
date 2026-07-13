<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Permit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PermitController extends Controller
{
    public function index(): JsonResponse
    {
        $permits = Permit::where('student_id', Auth::id())
            ->latest()
            ->get()
            ->map(fn($p) => [
                'id'             => $p->id,
                'type'           => $p->type,
                'type_label'     => $p->typeLabel(),
                'start_date'     => $p->start_date->toDateString(),
                'end_date'       => $p->end_date->toDateString(),
                'reason'         => $p->reason,
                'status'         => $p->status,
                'status_label'   => $this->statusLabel($p->status),
                'rejection_note' => $p->rejection_note,
                'file_url'       => $p->file ? Storage::disk('public')->url($p->file) : null,
                'created_at'     => $p->created_at->toIso8601String(),
            ]);

        return response()->json(['permits' => $permits]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'type'       => 'required|in:izin,sakit,dispensasi',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date'   => 'required|date|after_or_equal:start_date',
            'reason'     => 'required|string|max:500',
            'file'       => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        /** @var \App\Models\User $student */
        $student = Auth::user();

        $filePath = null;
        if ($request->hasFile('file')) {
            $filePath = $request->file('file')->store('permits', 'public');
        }

        $permit = Permit::create([
            'student_id' => $student->id,
            'type'       => $data['type'],
            'start_date' => $data['start_date'],
            'end_date'   => $data['end_date'],
            'reason'     => $data['reason'],
            'file'       => $filePath,
            'status'     => 'pending',
        ]);

        return response()->json([
            'message' => 'Pengajuan ' . $permit->typeLabel() . ' berhasil dikirim.',
            'permit'  => [
                'id'           => $permit->id,
                'type'         => $permit->type,
                'type_label'   => $permit->typeLabel(),
                'start_date'   => $permit->start_date->toDateString(),
                'end_date'     => $permit->end_date->toDateString(),
                'reason'       => $permit->reason,
                'status'       => $permit->status,
                'status_label' => $this->statusLabel($permit->status),
                'file_url'     => $permit->file ? Storage::disk('public')->url($permit->file) : null,
                'created_at'   => $permit->created_at->toIso8601String(),
            ],
        ], 201);
    }

    public function destroy(int $id): JsonResponse
    {
        $permit = Permit::where('student_id', Auth::id())->findOrFail($id);

        if (! $permit->isPending()) {
            return response()->json([
                'message' => 'Tidak dapat menghapus pengajuan yang sudah diproses.',
            ], 422);
        }

        if ($permit->file) {
            Storage::disk('public')->delete($permit->file);
        }

        $permit->delete();

        return response()->json(['message' => 'Pengajuan berhasil dihapus.']);
    }

    private function statusLabel(string $status): string
    {
        return match ($status) {
            'pending'  => 'Menunggu',
            'approved' => 'Disetujui',
            'rejected' => 'Ditolak',
            default    => ucfirst($status),
        };
    }
}
