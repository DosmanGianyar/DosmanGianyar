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
     * Generate spreadsheet object matching the matrix timetable layout.
     */
    public function generateSpreadsheet(?string $academicYear = null): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('JADWAL GURU');

        // Title on B3
        $sheet->setCellValue('B3', 'JADWAL GURU');
        $sheet->getStyle('B3')->getFont()->setBold(true)->setSize(14);

        // Header Rows: Row 4 (Days), Row 5 (Jam), Row 6 (Waktu)
        $startColIndex = 3; // Col C
        $periodsPerDay = 11;

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

        // Set Class Column Dimension (Col B)
        $sheet->getColumnDimension('B')->setWidth(12);

        // Fetch Classes ordered by name (excluding non-student dummy classes)
        $classes = SchoolClass::where('name', '!=', 'Review')
            ->orderBy('name')
            ->get();

        // Fetch Schedule Data
        $query = Schedule::with(['teacher', 'schoolClass']);
        if ($academicYear) {
            $query->where('academic_year', $academicYear);
        }
        $schedules = $query->get();

        // Map schedules by [class_id][day][period] => teacher_code
        $scheduleGrid = [];
        foreach ($schedules as $sch) {
            $code = $sch->teacher?->teacher_code ?: ($sch->teacher?->name ?? '');
            if (!empty($code)) {
                $scheduleGrid[$sch->class_id][$sch->day][$sch->period] = $code;
            }
        }

        // Populate Class Rows (Row 7 downwards)
        $currentRow = 7;
        foreach ($classes as $class) {
            $sheet->setCellValue("B{$currentRow}", $class->name);
            $sheet->getStyle("B{$currentRow}")->getFont()->setBold(true);
            $sheet->getStyle("B{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            foreach (self::DAYS as $dayNum => $dayName) {
                $dayStartColIdx = $startColIndex + ($dayNum - 1) * $periodsPerDay;

                for ($p = 1; $p <= $periodsPerDay; $p++) {
                    $colIdx    = $dayStartColIdx + ($p - 1);
                    $colLetter = Coordinate::stringFromColumnIndex($colIdx);

                    $teacherCode = $scheduleGrid[$class->id][$dayNum][$p] ?? '';
                    if (!empty($teacherCode)) {
                        $sheet->setCellValue("{$colLetter}{$currentRow}", $teacherCode);
                        $sheet->getStyle("{$colLetter}{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    }
                }
            }

            $currentRow++;
        }

        $lastRow = $currentRow - 1;
        $lastColLetter = Coordinate::stringFromColumnIndex($startColIndex + (6 * $periodsPerDay) - 1);

        // Apply grid borders to table
        $tableRange = "B4:{$lastColLetter}{$lastRow}";
        $sheet->getStyle($tableRange)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        // Make headers prominent
        $sheet->getStyle("B4:{$lastColLetter}6")->getBorders()->getOutline()->setBorderStyle(Border::BORDER_MEDIUM);

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

