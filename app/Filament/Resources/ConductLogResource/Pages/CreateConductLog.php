<?php

namespace App\Filament\Resources\ConductLogResource\Pages;

use App\Filament\Resources\ConductLogResource;
use Filament\Resources\Pages\CreateRecord;

class CreateConductLog extends CreateRecord
{
    protected static string $resource = ConductLogResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Bersihkan field yang tidak relevan dengan jenis catatan
        if ($data['type'] === 'pelanggaran') {
            $data['prestasi_type'] = null;
            $data['category_id']   = null;
            $data['lomba_name']    = null;
            $data['lomba_level']   = null;
            $data['lomba_rank']    = null;
        } elseif (($data['prestasi_type'] ?? null) === 'perilaku') {
            $data['description']  = null;
            $data['severity']     = null;
            $data['lomba_name']   = null;
            $data['lomba_level']  = null;
            $data['lomba_rank']   = null;
        } else {
            // lomba
            $data['description'] = null;
            $data['severity']    = null;
            $data['category_id'] = null;
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        /** @var \App\Models\ConductLog $record */
        $record = $this->record;
        $student = \App\Models\User::find($record->student_id);

        if ($student) {
            $isPrestasi = $record->isPrestasi();
            $title = $isPrestasi ? 'Catatan Positif Siswa' : 'Catatan Negatif Siswa';
            $body = $record->displayCategoryName() . ($record->note ? " — {$record->note}" : '');
            $type = $isPrestasi ? 'success' : 'warning';

            // 1. Send to Student
            \App\Services\NotificationService::send($student->id, $title, $body, $type, route('siswa.conduct.index'));

            // 2. Send to Parents
            \App\Services\NotificationService::notifyParentsOfStudent($student, "Catatan Perilaku Anak", "Ananda {$student->name}: {$body}", $type);

            // 3. Send to Homeroom Teacher
            \App\Services\NotificationService::notifyHomeroomTeacher($student, "Catatan Perilaku Siswa Kelas", "Siswa {$student->name}: {$body}", $type);
        }
    }
}
