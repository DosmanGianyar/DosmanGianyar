<?php

namespace App\Services;

use App\Models\Schedule;
use App\Models\SchoolClass;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ScheduleMatrixExport
{
    public const DAYS = [
        1 => 'Senin',
        2 => 'Selasa',
        3 => 'Rabu',
        4 => 'Kamis',
        5 => 'Jumat',
        6 => 'Sabtu',
    ];

    public const DEFAULT_TIMES = [
        1  => '7:10 - 7:55',
        2  => '7:30 - 8:15',
        3  => '8:15 - 9:00',
        4  => '9:00 - 9:45',
        5  => '10:00 - 10:45',
        6  => '10:45 - 11:30',
        7  => '11:30 - 12:15',
        8  => '12:30 - 13:15',
        9  => '13:15 - 14:00',
        10 => '16:00 - 16:45',
        11 => '17:00 - 17:45',
    ];

    /**
     * Generate spreadsheet object matching the matrix timetable layout with Mapel & Guru rows.
     */
    public function generateSpreadsheet(?string $academicYear = null): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('JADWAL GURU');

        // Title on A3
        $sheet->setCellValue('A3', 'JADWAL GURU');
        $sheet->getStyle('A3')->getFont()->setBold(true)->setSize(14);

        // Header Rows: Row 4 (Days), Row 5 (Jam), Row 6 (Waktu)
        $startColIndex = 3; // Col C
        $periodsPerDay = 11;

        // Class Column (Col A) and Type Column (Col B)
        $sheet->mergeCells('A4:A6');
        $sheet->setCellValue('A4', 'KELAS');
        $sheet->getStyle('A4')->getFont()->setBold(true)->setSize(11);
        $sheet->getStyle('A4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);

        $sheet->mergeCells('B4:B6');
        $sheet->setCellValue('B4', 'TIPE');
        $sheet->getStyle('B4')->getFont()->setBold(true)->setSize(11);
        $sheet->getStyle('B4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);

        $dayColors = [
            1 => 'D9E1F2', // Soft Blue
            2 => 'E2EFDA', // Soft Green
            3 => 'FFF2CC', // Soft Yellow
            4 => 'FCE4D6', // Soft Orange
            5 => 'EAEAEA', // Soft Gray
            6 => 'DDEBF7', // Soft Cyan
        ];

        foreach (self::DAYS as $dayNum => $dayName) {
            $dayStartColIdx = $startColIndex + ($dayNum - 1) * $periodsPerDay;
            $dayEndColIdx   = $dayStartColIdx + $periodsPerDay - 1;

            $startColLetter = Coordinate::stringFromColumnIndex($dayStartColIdx);
            $endColLetter   = Coordinate::stringFromColumnIndex($dayEndColIdx);

            // Merge Day Header (Row 4)
            $sheet->mergeCells("{$startColLetter}4:{$endColLetter}4");
            $sheet->setCellValue("{$startColLetter}4", $dayName);
            $sheet->getStyle("{$startColLetter}4")->getFont()->setBold(true)->setSize(11);
            $sheet->getStyle("{$startColLetter}4")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $fillColor = $dayColors[$dayNum] ?? 'F2F2F2';
            $sheet->getStyle("{$startColLetter}4:{$endColLetter}4")->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setRGB($fillColor);

            // Fill Periods (Row 5) and Times (Row 6)
            for ($p = 1; $p <= $periodsPerDay; $p++) {
                $colIdx    = $dayStartColIdx + ($p - 1);
                $colLetter = Coordinate::stringFromColumnIndex($colIdx);

                $sheet->setCellValue("{$colLetter}5", $p);
                $sheet->getStyle("{$colLetter}5")->getFont()->setBold(true);
                $sheet->getStyle("{$colLetter}5")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $timeText = self::DEFAULT_TIMES[$p] ?? '';
                $sheet->setCellValue("{$colLetter}6", $timeText);
                $sheet->getStyle("{$colLetter}6")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("{$colLetter}6")->getFont()->setSize(9);

                // Auto width with minimum
                $sheet->getColumnDimension($colLetter)->setWidth(13);
            }
        }

        // Set Class & Type Column Dimensions
        $sheet->getColumnDimension('A')->setWidth(12);
        $sheet->getColumnDimension('B')->setWidth(10);

        // Fetch Classes ordered by name (excluding non-student dummy classes)
        $classes = SchoolClass::where('name', '!=', 'Review')
            ->orderBy('name')
            ->get();

        // Fetch Schedule Data
        $query = Schedule::with(['teacher', 'schoolClass', 'subject']);
        if ($academicYear) {
            $query->where('academic_year', $academicYear);
        }
        $schedules = $query->get();

        // Map schedules by [class_id][day][period] => ['teacher' => ..., 'subject' => ...]
        $scheduleGrid = [];
        foreach ($schedules as $sch) {
            $teacherCode = $sch->teacher?->teacher_code ?: ($sch->teacher?->name ?? '');
            $subjectCode = $sch->subject?->code ?: ($sch->subject?->name ?? '');
            if (!empty($teacherCode) || !empty($subjectCode)) {
                $scheduleGrid[$sch->class_id][$sch->day][$sch->period] = [
                    'teacher' => $teacherCode,
                    'subject' => $subjectCode,
                ];
            }
        }

        // Populate Class Rows (2 rows per class: Row 1 = Mapel, Row 2 = Guru)
        $currentRow = 7;
        foreach ($classes as $class) {
            $guruRow = $currentRow + 1;

            // Row 1: Mapel
            $sheet->setCellValue("A{$currentRow}", $class->name);
            $sheet->setCellValue("B{$currentRow}", 'Mapel');
            $sheet->getStyle("A{$currentRow}:B{$currentRow}")->getFont()->setBold(true);
            $sheet->getStyle("A{$currentRow}:B{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            // Row 2: Guru
            $sheet->setCellValue("A{$guruRow}", '');
            $sheet->setCellValue("B{$guruRow}", 'Guru');
            $sheet->getStyle("B{$guruRow}")->getFont()->setBold(true);
            $sheet->getStyle("A{$guruRow}:B{$guruRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            foreach (self::DAYS as $dayNum => $dayName) {
                $dayStartColIdx = $startColIndex + ($dayNum - 1) * $periodsPerDay;

                for ($p = 1; $p <= $periodsPerDay; $p++) {
                    $colIdx    = $dayStartColIdx + ($p - 1);
                    $colLetter = Coordinate::stringFromColumnIndex($colIdx);

                    $slot = $scheduleGrid[$class->id][$dayNum][$p] ?? null;
                    if ($slot) {
                        if (!empty($slot['subject'])) {
                            $sheet->setCellValue("{$colLetter}{$currentRow}", $slot['subject']);
                            $sheet->getStyle("{$colLetter}{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                        }
                        if (!empty($slot['teacher'])) {
                            $sheet->setCellValue("{$colLetter}{$guruRow}", $slot['teacher']);
                            $sheet->getStyle("{$colLetter}{$guruRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                        }
                    }
                }
            }

            $currentRow += 2;
        }

        $lastRow = max($currentRow - 1, 7);
        $lastColLetter = Coordinate::stringFromColumnIndex($startColIndex + (6 * $periodsPerDay) - 1);

        // Apply grid borders to table
        $tableRange = "A4:{$lastColLetter}{$lastRow}";
        $sheet->getStyle($tableRange)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        // Make headers prominent
        $sheet->getStyle("A4:{$lastColLetter}6")->getBorders()->getOutline()->setBorderStyle(Border::BORDER_MEDIUM);

        return $spreadsheet;
    }

    /**
     * Download as stream response.
     */
    public function download(?string $academicYear = null, ?string $filename = null): StreamedResponse
    {
        $filename = $filename ?: 'jadwal-pelajaran-' . date('Ymd-His') . '.xlsx';
        $spreadsheet = $this->generateSpreadsheet($academicYear);

        return new StreamedResponse(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, 200, [
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Cache-Control'       => 'max-age=0',
        ]);
    }
}
