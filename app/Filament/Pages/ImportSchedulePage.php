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
    protected static ?string                 $navigationLabel = 'Import Jadwal PDF/Excel';
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

    public string $selectedGrade = '10';
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
                    ->helperText('Jadwal lama pada tahun ajaran ini akan otomatis dibersihkan dan digantikan dengan jadwal baru dari PDF.'),

                FileUpload::make('file')
                    ->label('File Master PDF / Excel Jadwal (aSc Timetables)')
                    ->helperText('Unggah file PDF master jadwal (berisi seluruh guru & kelas). Sistem otomatis membaca semua kelas 10, 11, 12.')
                    ->acceptedFileTypes([
                        'application/pdf',
                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        'application/vnd.ms-excel',
                        'text/csv',
                    ])
                    ->maxSize(20480) // 20MB
                    ->required()
                    ->storeFiles(false),
            ])
            ->statePath('data');
    }

    /**
     * Langkah 1: Parsing file PDF / Excel Master
     */
    public function startParsing(ScheduleImportService $service): void
    {
        $state        = $this->form->getState();
        $uploadedFile = $state['file'] ?? null;

        if (! $uploadedFile) {
            Notification::make()->title('File tidak ditemukan')->danger()->send();
            return;
        }

        $this->academicYear    = $state['academic_year'] ?? '2026/2027 Ganjil';
        $this->replaceExisting = (bool) ($state['replace_existing'] ?? true);
        $this->selectedGrade   = 'Semua Kelas (10, 11, 12)';

        try {
            $filePath = $uploadedFile->getRealPath();
            $mimeType = $uploadedFile->getMimeType();

            $items = $service->parseFile($filePath, $mimeType, 'ALL');

            if (empty($items)) {
                Notification::make()
                    ->title('Gagal membaca jadwal dari file')
                    ->body('Pastikan file PDF/Excel memiliki format tabel aSc Timetables yang valid.')
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

            // Update item yang bersangkutan di parsedItems
            foreach ($this->parsedItems as &$item) {
                if ($item['temp_id'] === $tempId || $item['teacher_raw'] === $rawName) {
                    $item['teacher_id']       = $newTeacher->id;
                    $item['teacher_name']     = $newTeacher->name;
                    $item['match_confidence'] = 'exact';
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
