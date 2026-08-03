<?php

namespace App\Filament\Pages;

use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\User;
use App\Services\ScheduleImportService;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Session;

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
    public bool $isParsed = false;

    public string $selectedGrade = 'Semua Kelas (10, 11, 12)';
    public string $academicYear = '2026/2027 Ganjil';
    public bool $replaceExisting = true;

    public string $filterClass = 'ALL';
    public string $filterStatus = 'ALL';
    public int $currentPage = 1;
    public int $perPage = 40;

    protected function getSessionKey(): string
    {
        return 'schedule_import_items_' . auth()->id();
    }

    public function mount(): void
    {
        $this->form->fill([
            'academic_year'    => '2026/2027 Ganjil',
            'replace_existing' => true,
        ]);

        if (Session::has($this->getSessionKey())) {
            $this->isParsed = true;
        }
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
                    ->helperText('Unggah file CSV/Excel master jadwal. Sistem otomatis mengekstrak hari, jam, kelas, mapel, dan melakukan pencocokan guru.')
                    ->acceptedFileTypes([
                        'text/csv',
                        'text/plain',
                        'application/csv',
                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        'application/vnd.ms-excel',
                    ])
                    ->maxSize(20480) // 20MB
                    ->required()
                    ->storeFiles(false),
            ])
            ->statePath('data');
    }

    /**
     * Langkah 1: Parsing file CSV / Excel yang diunggah
     */
    public function startParsing(ScheduleImportService $service): void
    {
        @ini_set('memory_limit', '512M');

        $state        = $this->form->getState();
        $uploadedFile = $state['file'] ?? null;

        if (! $uploadedFile) {
            Notification::make()->title('Silakan pilih file CSV / Excel terlebih dahulu')->warning()->send();
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
                    ->body('Pastikan file CSV / Excel memiliki format tabel atau matriks jadwal yang valid.')
                    ->warning()
                    ->send();
                return;
            }

            Session::put($this->getSessionKey(), $items);
            $this->isParsed     = true;
            $this->currentPage  = 1;
            $this->filterClass  = 'ALL';
            $this->filterStatus = 'ALL';

            Notification::make()
                ->title('File Berhasil Diparsing!')
                ->body('Ditemukan ' . count($items) . ' slot jadwal pelajaran. Silakan periksa & koreksi tabel pratinjau di bawah.')
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

    public function getAllSessionItems(): array
    {
        return Session::get($this->getSessionKey(), []);
    }

    public function getFilteredItems(): array
    {
        $all = $this->getAllSessionItems();

        return array_filter($all, function ($item) {
            if ($this->filterClass !== 'ALL' && $item['class_name'] !== $this->filterClass) {
                return false;
            }
            if ($this->filterStatus === 'unmatched_teacher' && !empty($item['teacher_id'])) {
                return false;
            }
            if ($this->filterStatus === 'unmatched_subject' && !empty($item['subject_id'])) {
                return false;
            }
            if ($this->filterStatus === 'unmatched_any' && (!empty($item['teacher_id']) && !empty($item['subject_id']))) {
                return false;
            }
            if ($this->filterStatus === 'matched_all' && (empty($item['teacher_id']) || empty($item['subject_id']))) {
                return false;
            }
            if ($this->filterStatus === 'unmatched' && !empty($item['teacher_id'])) {
                return false;
            }
            if ($this->filterStatus === 'matched' && empty($item['teacher_id'])) {
                return false;
            }
            return true;
        });
    }

    public function getPaginatedItems(): array
    {
        $filtered = array_values($this->getFilteredItems());
        $offset   = ($this->currentPage - 1) * $this->perPage;
        return array_slice($filtered, $offset, $this->perPage);
    }

    public function getTotalPages(): int
    {
        $count = count($this->getFilteredItems());
        return max(1, (int) ceil($count / $this->perPage));
    }

    public function nextPage(): void
    {
        if ($this->currentPage < $this->getTotalPages()) {
            $this->currentPage++;
        }
    }

    public function previousPage(): void
    {
        if ($this->currentPage > 1) {
            $this->currentPage--;
        }
    }

    public function updatedFilterClass(): void
    {
        $this->currentPage = 1;
    }

    public function updatedFilterStatus(): void
    {
        $this->currentPage = 1;
    }

    public function updateItemRow(string $tempId, string $field, $value): void
    {
        $items       = $this->getAllSessionItems();
        $service     = new ScheduleImportService();
        $allSubjects = Subject::all();

        foreach ($items as &$item) {
            if ($item['temp_id'] === $tempId) {
                if ($field === 'teacher_id') {
                    $item['teacher_id'] = $value ?: null;
                    if ($value) {
                        $teacher = User::find($value);
                        if ($teacher) {
                            $item['teacher_name']     = $teacher->name;
                            $item['match_confidence'] = 'exact';
                            $res = $service->resolveSubjectForTeacher($teacher, $allSubjects, $item['subject_code'] ?? null);
                            if ($res['subject_id']) {
                                $item['subject_id'] = $res['subject_id'];
                            }
                            $item['allowed_subject_ids'] = $res['allowed_subject_ids'];
                        }
                    } else {
                        $item['match_confidence'] = 'unmatched';
                    }
                } elseif ($field === 'class_id') {
                    $item['class_id'] = $value ?: null;
                    $c = SchoolClass::find($value);
                    if ($c) {
                        $item['class_name'] = $c->name;
                    }
                } elseif ($field === 'subject_id') {
                    $item['subject_id'] = $value ?: null;
                    $s = Subject::find($value);
                    if ($s) {
                        $item['subject_name'] = $s->name;
                    }
                } elseif ($field === 'day') {
                    $item['day'] = (int) $value;
                } elseif ($field === 'period') {
                    $period = (int) $value;
                    $timeSlots = ScheduleImportService::TIME_SLOTS;
                    if (isset($timeSlots[$period])) {
                        $item['period']     = $period;
                        $item['start_time'] = $timeSlots[$period][0];
                        $item['end_time']   = $timeSlots[$period][1];
                    }
                }
                break;
            }
        }

        Session::put($this->getSessionKey(), $items);
    }

    public function createTeacherInline(string $tempId, string $rawName, ScheduleImportService $service): void
    {
        try {
            $newTeacher  = $service->createDraftTeacher($rawName);
            $items       = $this->getAllSessionItems();
            $allSubjects = Subject::all();

            foreach ($items as &$item) {
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

            Session::put($this->getSessionKey(), $items);

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

    public function createAllUnmatchedTeachers(ScheduleImportService $service): void
    {
        $items = $this->getAllSessionItems();
        if (empty($items)) return;

        try {
            $createdCount = 0;
            $allSubjects  = Subject::all();
            $unmatchedRaw = [];

            foreach ($items as $item) {
                if (empty($item['teacher_id']) && !empty($item['teacher_raw'])) {
                    $unmatchedRaw[$item['teacher_raw']] = true;
                }
            }

            $createdTeachers = [];
            foreach (array_keys($unmatchedRaw) as $rawName) {
                $teacher = $service->createDraftTeacher($rawName);
                $createdTeachers[$rawName] = $teacher;
                $createdCount++;
            }

            foreach ($items as &$item) {
                $rawName = $item['teacher_raw'] ?? '';
                if (isset($createdTeachers[$rawName])) {
                    $newTeacher = $createdTeachers[$rawName];
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

            Session::put($this->getSessionKey(), $items);

            Notification::make()
                ->title('Berhasil Membuat Akun Guru Massal')
                ->body("Sebanyak {$createdCount} akun guru baru berhasil dibuat dan otomatis terhubung ke pratinjau jadwal.")
                ->success()
                ->send();
        } catch (\Throwable $e) {
            Notification::make()
                ->title('Gagal Buat Akun Guru Massal')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function saveToDatabase(ScheduleImportService $service): void
    {
        @ini_set('memory_limit', '512M');

        $items = $this->getAllSessionItems();

        if (empty($items)) {
            Notification::make()->title('Tidak ada data jadwal untuk disimpan')->warning()->send();
            return;
        }

        try {
            $count = $service->saveSchedules($items, $this->academicYear, $this->replaceExisting);

            Session::forget($this->getSessionKey());
            $this->isParsed = false;
            $this->form->fill();

            Notification::make()
                ->title('Jadwal Pelajaran Berhasil Disimpan!')
                ->body("Sebanyak {$count} slot jadwal telah berhasil tersimpan di database.")
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

    public function cancelPreview(): void
    {
        Session::forget($this->getSessionKey());
        $this->isParsed = false;
    }
}
