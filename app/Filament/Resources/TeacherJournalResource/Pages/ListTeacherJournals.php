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
            Action::make('printWeeklyJournal')
                ->label('Cetak Jurnal Perminggu (PDF)')
                ->icon('heroicon-o-document-text')
                ->color('info')
                ->form([
                    Select::make('teacher_id')
                        ->label('Pilih Guru')
                        ->options(User::where('role', 'guru')->pluck('name', 'id'))
                        ->searchable()
                        ->required(),
                    DatePicker::make('week_date')
                        ->label('Tanggal Minggu')
                        ->default(now()->toDateString())
                        ->required(),
                    Select::make('class_id')
                        ->label('Kelas (Opsional)')
                        ->options(SchoolClass::pluck('name', 'id')),
                ])
                ->action(function (array $data) {
                    $params = array_filter($data);
                    $url = route('guru.journal.print-weekly', $params);
                    $this->js("window.open('{$url}', '_blank');");
                }),

            Action::make('printWeeklyAttendance')
                ->label('Cetak Absen Perminggu (PDF)')
                ->icon('heroicon-o-user-group')
                ->color('success')
                ->form([
                    Select::make('teacher_id')
                        ->label('Pilih Guru')
                        ->options(User::where('role', 'guru')->pluck('name', 'id'))
                        ->searchable()
                        ->required(),
                    DatePicker::make('week_date')
                        ->label('Tanggal Minggu')
                        ->default(now()->toDateString())
                        ->required(),
                    Select::make('class_id')
                        ->label('Kelas')
                        ->options(SchoolClass::pluck('name', 'id')),
                ])
                ->action(function (array $data) {
                    $params = array_filter($data);
                    $url = route('guru.journal.print-weekly-attendance', $params);
                    $this->js("window.open('{$url}', '_blank');");
                }),
        ];
    }
}
