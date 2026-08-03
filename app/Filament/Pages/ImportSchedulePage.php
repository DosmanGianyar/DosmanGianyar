<?php

namespace App\Filament\Pages;

use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\User;
use App\Services\ScheduleImportService;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;

class ImportSchedulePage extends Page
{
    protected static string|\BackedEnum|null $navigationIcon  = 'heroicon-o-calendar';
    protected static string|\UnitEnum|null   $navigationGroup = 'Kurikulum';
    protected static ?string                 $navigationLabel = 'Import Jadwal CSV/Excel';
    protected static ?string                 $slug            = 'import-schedule';
    protected static ?int                    $navigationSort  = 5;

    public static function canAccess(): bool
    {
        return auth()->user()?->role === 'admin' || auth()->user()?->role === 'pengelola';
    }

    protected string $view = 'filament.pages.import-schedule';

    public ?array $data = [];
    public ?array $parsedItems = null;
    public bool $isParsed = false;

    public string $selectedGrade = '10, 11, 12';
    public string $academicYear = '2026/2027 Ganjil';
    public bool $replaceExisting = true;

    public function mount(): void
    {
        $this->form->fill([
            'academic_year'    => '2026/2027 Ganjil',
            'replace_existing' => true,
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('academic_year')
                    ->label('Tahun Ajaran & Semester')
                    ->default('2026/2027 Ganjil')
                    ->required()
                    ->helperText('Jadwal baru akan disimpan untuk tahun ajaran ini.'),

                Toggle::make('replace_existing')
                    ->label('Otomatis Hapus & Gantikan Jadwal Lama')
                    ->default(true)
                    ->helperText('Jadwal lama pada tahun ajaran ini akan otomatis dibersihkan dan digantikan dengan jadwal baru.'),

                FileUpload::make('file')
                    ->label('File Master CSV / Excel Jadwal (.csv, .xlsx, .xls)')
                    ->helperText('Unggah file CSV master jadwal (misal: JADWAL GURU_MAPEL HOR.csv). Sistem otomatis mengekstrak hari, jam, kelas, mapel, dan melakukan matching guru.')
                    ->acceptedFileTypes([
                        'text/csv',
                        'text/plain',
                        'application/csv',
                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        'application/vnd.ms-excel',
                    ])
                    ->maxSize(20480) // 20MB
                    ->nullable()
                    ->storeFiles(false),
            ])
            ->statePath('data');
    }

    /**
     * Langkah 1A: Parse langsung file default public/JADWAL GURU_MAPEL HOR.csv
     */
    public function parseDefaultCsv(ScheduleImportService $service): void
    {
        $state                 = $this->form->getState();
        $this->academicYear    = $state['academic_year'] ?? '2026/2027 Ganjil';
        $this->replaceExisting = (bool) ($state['replace_existing'] ?? true);
        $this->selectedGrade   = 'Semua Kelas (10, 11, 12)';

        $defaultCsvPath = public_path('JADWAL GURU_MAPEL HOR.csv');

        if (!file_exists($defaultCsvPath)) {
            Notification::make()
                ->title('File Default Tidak Ditemukan')
                ->body("File 'public/JADWAL GURU_MAPEL HOR.csv' tidak ditemukan di server.")
                ->danger()
                ->send();
            return;
        }

        try {
            $items = $service->parseCsvSchedule($defaultCsvPath, 'ALL');

            if (empty($items)) {
                Notification::make()
                    ->title('Gagal membaca jadwal dari file CSV default')
                    ->body('Pastikan file CSV memiliki format tabel/grid jadwal pelajaran yang valid.')
                    ->warning()
                    ->send();
                return;
            }

            $this->parsedItems = $items;
            $this->isParsed    = true;

            Notification::make()
                ->title('File CSV Default Berhasil Diparsing!')
                ->body('Ditemukan ' . count($items) . ' slot jadwal pelajaran master dari file JADWAL GURU_MAPEL HOR.csv.')
                ->success()
                ->send();
        } catch (\Throwable $e) {
            Notification::make()
                ->title('Error Parsing File Default')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    /**
     * Langkah 1B: Parsing file CSV / Excel yang diunggah
     */
    public function startParsing(ScheduleImportService $service): void
    {
        $state        = $this->form->getState();
        $uploadedFile = $state['file'] ?? null;

        if (! $uploadedFile) {
            // Jika tidak ada file di-upload, jalankan parseDefaultCsv
            $this->parseDefaultCsv($service);
            return;
        }

        $this->academicYear    = $state['academic_year'] ?? '2026/2027 Ganjil';
        $this->replaceExisting = (bool) ($state['replace_existing'] ?? true);
        $this->selectedGrade   = 'Semua Kelas (10, 11, 12)';

        try {
            $filePath = $uploadedFile->getRealPath();
            $items    = $service->parseCsvSchedule($filePath, 'ALL');

            if (empty($items)) {
                Notification::make()
                    ->title('Gagal membaca jadwal dari file CSV / Excel')
                    ->body('Pastikan file CSV / Excel memiliki format tabel/grid jadwal pelajaran yang valid.')
                    ->warning()
                    ->send();
                return;
            }

            $this->parsedItems = $items;
            $this->isParsed    = true;

            Notification::make()
                ->title('File Berhasil Diparsing!')
                ->body('Ditemukan ' . count($items) . ' slot jadwal pelajaran master. Silakan periksa tabel pratinjau di bawah.')
                ->success()
                ->send();
        } catch (\Throwable $e) {
            Notification::make()
                ->title('Error Parsing File')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    /**
     * Buat akun Guru baru secara instan jika belum ada di database
     */
    public function createTeacherInline(string $tempId, string $rawName, ScheduleImportService $service): void
    {
        try {
            $newTeacher = $service->createDraftTeacher($rawName);

            $allSubjects = Subject::all();
            foreach ($this->parsedItems as &$item) {
                if ($item['temp_id'] === $tempId || $item['teacher_raw'] === $rawName) {
                    $item['teacher_id']       = $newTeacher->id;
                    $item['teacher_name']     = $newTeacher->name;
                    $item['match_confidence'] = 'exact';

                    $res = $service->resolveSubjectForTeacher($newTeacher, $allSubjects, $item['subject_code'] ?? null);
                    if ($res['subject_id']) {
                        $item['subject_id'] = $res['subject_id'];
                    }
                    $item['allowed_subject_ids'] = $res['allowed_subject_ids'];
                }
            }

            Notification::make()
                ->title('Akun Guru Berhasil Dibuat')
                ->body("Akun untuk '{$newTeacher->name}' telah ditambahkan ke database.")
                ->success()
                ->send();
        } catch (\Throwable $e) {
            Notification::make()
                ->title('Gagal Buat Akun Guru')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function updatedParsedItems($value, $key): void
    {
        if (str_contains($key, '.teacher_id') && ! empty($value)) {
            $parts     = explode('.', $key);
            $idx       = (int) $parts[0];
            $teacherId = $value;

            $teacher     = User::find($teacherId);
            $allSubjects = Subject::all();

            if ($teacher) {
                $res = (new ScheduleImportService())->resolveSubjectForTeacher(
                    $teacher,
                    $allSubjects,
                    $this->parsedItems[$idx]['subject_code'] ?? null
                );

                if ($res['subject_id']) {
                    $this->parsedItems[$idx]['subject_id'] = $res['subject_id'];
                }
                $this->parsedItems[$idx]['allowed_subject_ids'] = $res['allowed_subject_ids'];
                $this->parsedItems[$idx]['teacher_name']        = $teacher->name;
            }
        }

        if (str_contains($key, '.period') && is_numeric($value)) {
            $parts  = explode('.', $key);
            $idx    = (int) $parts[0];
            $period = (int) $value;

            $timeSlots = ScheduleImportService::TIME_SLOTS;
            if (isset($timeSlots[$period])) {
                $this->parsedItems[$idx]['period']     = $period;
                $this->parsedItems[$idx]['start_time'] = $timeSlots[$period][0];
                $this->parsedItems[$idx]['end_time']   = $timeSlots[$period][1];
            }
        }

        if (str_contains($key, '.day') && is_numeric($value)) {
            $parts = explode('.', $key);
            $idx   = (int) $parts[0];
            $this->parsedItems[$idx]['day'] = (int) $value;
        }
    }

    /**
     * Langkah 2: Simpan data terverifikasi ke Database
     */
    public function saveToDatabase(ScheduleImportService $service): void
    {
        if (empty($this->parsedItems)) {
            Notification::make()->title('Tidak ada data jadwal untuk disimpan')->warning()->send();
            return;
        }

        try {
            $count = $service->saveSchedules($this->parsedItems, $this->academicYear, $this->replaceExisting);

            $this->isParsed    = false;
            $this->parsedItems = null;
            $this->form->fill();

            Notification::make()
                ->title('Jadwal Pelajaran Berhasil Disimpan!')
                ->body("Sebanyak {$count} slot jadwal kelas {$this->selectedGrade} telah masuk ke database.")
                ->success()
                ->persistent()
                ->send();
        } catch (\Throwable $e) {
            Notification::make()
                ->title('Gagal Menyimpan Jadwal')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    /** Reset state pratinjau */
    public function cancelPreview(): void
    {
        $this->isParsed    = false;
        $this->parsedItems = null;
    }
}
