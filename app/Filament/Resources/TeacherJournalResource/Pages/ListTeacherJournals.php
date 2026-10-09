<?php

namespace App\Filament\Resources\TeacherJournalResource\Pages;

use App\Filament\Resources\TeacherJournalResource;
use App\Models\SchoolClass;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Resources\Pages\ListRecords;

class ListTeacherJournals extends ListRecords
{
    protected static string $resource = TeacherJournalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('printTeacherJournal')
                ->label('Cetak Jurnal (PDF)')
                ->icon('heroicon-o-printer')
                ->color('primary')
                ->modalHeading('Cetak Jurnal Mengajar (A4 Landscape)')
                ->form([
                    Select::make('teacher_id')
                        ->label('Pilih Guru')
                        ->options(User::where('role', 'guru')->orderBy('name')->pluck('name', 'id'))
                        ->searchable()
                        ->required(),
                    Select::make('class_id')
                        ->label('Kelas (Opsional)')
                        ->options(SchoolClass::orderBy('name')->pluck('name', 'id'))
                        ->searchable(),
                    DatePicker::make('start_date')
                        ->label('Dari Tanggal (Opsional)'),
                    DatePicker::make('end_date')
                        ->label('Sampai Tanggal (Opsional)'),
                    Select::make('month')
                        ->label('Bulan (Opsional)')
                        ->options([
                            '1'  => 'Januari',
                            '2'  => 'Februari',
                            '3'  => 'Maret',
                            '4'  => 'April',
                            '5'  => 'Mei',
                            '6'  => 'Juni',
                            '7'  => 'Juli',
                            '8'  => 'Agustus',
                            '9'  => 'September',
                            '10' => 'Oktober',
                            '11' => 'November',
                            '12' => 'Desember',
                        ]),
                    Select::make('year')
                        ->label('Tahun (Opsional)')
                        ->options(array_combine(range(date('Y'), date('Y') - 3), range(date('Y'), date('Y') - 3))),
                ])
                ->action(function (array $data) {
                    $params = array_filter($data);
                    $url = route('guru.journal.print', $params);
                    $this->js("window.open('{$url}', '_blank');");
                }),

            Action::make('printWeeklyAttendance')
                ->label('Cetak Rekap Absen')
                ->icon('heroicon-o-user-group')
                ->color('success')
                ->modalHeading('Cetak Rekap Absensi Siswa')
                ->form([
                    Select::make('teacher_id')
                        ->label('Pilih Guru')
                        ->options(User::where('role', 'guru')->orderBy('name')->pluck('name', 'id'))
                        ->searchable()
                        ->required(),
                    Select::make('class_id')
                        ->label('Kelas (Opsional)')
                        ->options(SchoolClass::orderBy('name')->pluck('name', 'id')),
                    Select::make('month')
                        ->label('Bulan (Opsional)')
                        ->options([
                            '1'  => 'Januari',
                            '2'  => 'Februari',
                            '3'  => 'Maret',
                            '4'  => 'April',
                            '5'  => 'Mei',
                            '6'  => 'Juni',
                            '7'  => 'Juli',
                            '8'  => 'Agustus',
                            '9'  => 'September',
                            '10' => 'Oktober',
                            '11' => 'November',
                            '12' => 'Desember',
                        ]),
                    Select::make('year')
                        ->label('Tahun (Opsional)')
                        ->options(array_combine(range(date('Y'), date('Y') - 3), range(date('Y'), date('Y') - 3))),
                ])
                ->action(function (array $data) {
                    $params = array_filter($data);
                    $url = route('guru.journal.print-weekly-attendance', $params);
                    $this->js("window.open('{$url}', '_blank');");
                }),
        ];
    }
}
