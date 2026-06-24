<?php

namespace App\Exports;

use App\Models\ExtracurricularAttendance;
use App\Models\ExtracurricularMember;
use App\Models\ExtracurricularSession;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ExtracurricularAttendanceExport implements
    FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithTitle
{
    public function __construct(private readonly ExtracurricularSession $session) {}

    public function collection()
    {
        // Ambil semua anggota aktif ekstra ini
        $activeMembers = ExtracurricularMember::with('user.schoolClass')
            ->where('extracurricular_id', $this->session->extracurricular_id)
            ->where('status', 'active')
            ->orderBy('user_id')
            ->get();

        // Buat lookup kehadiran per user
        $attendances = ExtracurricularAttendance::where('session_id', $this->session->id)
            ->pluck('status', 'user_id');

        // Gabungkan: setiap anggota + status kehadirannya
        return $activeMembers->map(function (ExtracurricularMember $member) use ($attendances) {
            $member->attendance_status = $attendances[$member->user_id] ?? 'alpa';
            return $member;
        });
    }

    public function headings(): array
    {
        return [
            'No',
            'Nama Siswa',
            'NIS',
            'Kelas',
            'Peran',
            'Status Kehadiran',
        ];
    }

    public function map($row): array
    {
        static $no = 0;
        $no++;

        return [
            $no,
            $row->user?->name,
            $row->user?->nis ?? '—',
            $row->user?->schoolClass?->name ?? '—',
            $row->roleLabel(),
            ucfirst($row->attendance_status),
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        // Baris info header sesi di atas tabel
        $sheet->insertNewRowBefore(1, 4);

        $sheet->setCellValue('A1', 'Rekap Absensi Ekstrakurikuler');
        $sheet->setCellValue('A2', $this->session->extracurricular->name ?? '—');
        $sheet->setCellValue('A3', 'Sesi : ' . $this->session->title);
        $sheet->setCellValue('A4', 'Tanggal : ' . $this->session->session_date->locale('id')->isoFormat('D MMMM Y'));

        $sheet->mergeCells('A1:F1');
        $sheet->mergeCells('A2:F2');
        $sheet->mergeCells('A3:F3');
        $sheet->mergeCells('A4:F4');

        return [
            1 => [
                'font'      => ['bold' => true, 'size' => 14, 'color' => ['argb' => 'FF1D4ED8']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
            2 => [
                'font'      => ['bold' => true, 'size' => 11],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
            5 => [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill' => ['fillType' => 'solid', 'startColor' => ['argb' => 'FF1D4ED8']],
            ],
        ];
    }

    public function columnWidths(): array
    {
        return ['A' => 6, 'B' => 30, 'C' => 14, 'D' => 14, 'E' => 12, 'F' => 18];
    }

    public function title(): string
    {
        return 'Rekap ' . $this->session->session_date->format('d-m-Y');
    }
}
