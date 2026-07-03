<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\StudentGrade;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GradeController extends Controller
{
    /**
     * Daftar nilai mentah per mata pelajaran, semester, tahun ajaran.
     */
    public function index(Request $request): JsonResponse
    {
        /** @var \App\Models\User $siswa */
        $siswa = Auth::user();

        $academicYear = $request->get('academic_year', StudentGrade::currentAcademicYear());
        $semester     = (int) $request->get('semester', StudentGrade::currentSemester());

        $grades = StudentGrade::with('subject', 'recorder')
            ->where('student_id', $siswa->id)
            ->where('academic_year', $academicYear)
            ->where('semester', $semester)
            ->orderBy('subject_id')
            ->orderBy('type')
            ->get();

        return response()->json([
            'academic_year' => $academicYear,
            'semester'      => $semester,
            'grades'        => $grades->map(fn ($g) => [
                'id'           => $g->id,
                'subject_name' => $g->subject->name,
                'subject_code' => $g->subject->code,
                'type'         => $g->type,
                'type_label'   => $g->typeLabel(),
                'score'        => (float) $g->score,
                'notes'        => $g->notes,
                'recorder'     => $g->recorder?->name,
                'created_at'   => $g->created_at->toDateString(),
            ])->values(),
        ]);
    }

    /**
     * Rekap nilai per mata pelajaran (digroup untuk tampilan rapor).
     */
    public function summary(Request $request): JsonResponse
    {
        /** @var \App\Models\User $siswa */
        $siswa = Auth::user();

        $academicYear = $request->get('academic_year', StudentGrade::currentAcademicYear());
        $semester     = (int) $request->get('semester', StudentGrade::currentSemester());

        $grades = StudentGrade::with('subject')
            ->where('student_id', $siswa->id)
            ->where('academic_year', $academicYear)
            ->where('semester', $semester)
            ->get();

        // Group by subject
        $bySubject = $grades->groupBy('subject_id');

        $subjects = $bySubject->map(function ($items) {
            $subject = $items->first()->subject;

            $uhScores  = $items->where('type', 'UH')->pluck('score')->map(fn ($s) => (float) $s);
            $utsScores = $items->where('type', 'UTS')->pluck('score')->map(fn ($s) => (float) $s);
            $uasScores = $items->where('type', 'UAS')->pluck('score')->map(fn ($s) => (float) $s);

            $uhAvg  = $uhScores->isNotEmpty()  ? round($uhScores->avg(),  2) : null;
            $uts    = $utsScores->isNotEmpty() ? (float) $utsScores->first() : null;
            $uas    = $uasScores->isNotEmpty() ? (float) $uasScores->first() : null;

            // Nilai akhir: rata-rata UH (40%) + UTS (30%) + UAS (30%)
            // Hanya hitung jika minimal ada satu nilai
            $scores = array_filter([$uhAvg, $uts, $uas], fn ($v) => $v !== null);
            $final  = count($scores) > 0 ? round(
                ($uhAvg  !== null ? $uhAvg * 0.40 : 0) +
                ($uts    !== null ? $uts    * 0.30 : 0) +
                ($uas    !== null ? $uas    * 0.30 : 0),
                2
            ) : null;

            return [
                'subject_id'   => $subject->id,
                'subject_name' => $subject->name,
                'subject_code' => $subject->code,
                'uh_scores'    => $uhScores->values(),
                'uh_avg'       => $uhAvg,
                'uts'          => $uts,
                'uas'          => $uas,
                'final_score'  => $final,
            ];
        })->sortBy('subject_name')->values();

        $classAvg = $subjects->whereNotNull('final_score')->avg('final_score');

        return response()->json([
            'academic_year' => $academicYear,
            'semester'      => $semester,
            'class_average' => $classAvg ? round($classAvg, 2) : null,
            'subjects'      => $subjects,
        ]);
    }
}
