<?php

namespace App\Exports;

use App\Models\StudentGrade;
use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class StudentGradeExport implements FromCollection, WithHeadings, ShouldAutoSize, WithTitle
{
    public function __construct(
        private readonly int    $classId,
        private readonly int    $semester,
        private readonly string $academicYear,
    ) {}

    public function collection()
    {
        $students = User::where('role', 'siswa')
            ->where('class_id', $this->classId)
            ->orderBy('name')
            ->get();

        $grades = StudentGrade::with('subject')
            ->whereIn('student_id', $students->pluck('id'))
            ->where('semester', $this->semester)
            ->where('academic_year', $this->academicYear)
            ->orderBy('subject_id')
            ->orderBy('type')
            ->get()
            ->groupBy('student_id');

        $rows = collect();
        foreach ($students as $student) {
            $studentGrades = $grades->get($student->id, collect());
            $bySubject     = $studentGrades->groupBy('subject_id');

            foreach ($bySubject as $subjectId => $sg) {
                $subject  = $sg->first()->subject;
                $uhScores = $sg->where('type', 'UH')->pluck('score');
                $uts      = $sg->firstWhere('type', 'UTS')?->score;
                $uas      = $sg->firstWhere('type', 'UAS')?->score;
                $uhAvg    = $uhScores->isNotEmpty() ? round($uhScores->average(), 1) : '-';

                $rows->push([
                    'Nama'          => $student->name,
                    'NIS'           => $student->nis,
                    'Mata Pelajaran'=> $subject->name,
                    'UH (Rata-rata)'=> $uhAvg,
                    'UTS'           => $uts ?? '-',
                    'UAS'           => $uas ?? '-',
                    'Semester'      => $this->semester,
                    'Tahun Ajaran'  => $this->academicYear,
                ]);
            }
        }

        return $rows;
    }

    public function headings(): array
    {
        return ['Nama', 'NIS', 'Mata Pelajaran', 'UH (Rata-rata)', 'UTS', 'UAS', 'Semester', 'Tahun Ajaran'];
    }

    public function title(): string
    {
        return 'Rekap Nilai';
    }
}
