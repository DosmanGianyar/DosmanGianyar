<?php

namespace App\Filament\Pages;

use App\Models\Extracurricular;
use App\Models\User;
use App\Services\ExtracurricularImportService;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\DB;
use Livewire\WithFileUploads;

class ImportExtracurricularPage extends Page
{
    use WithFileUploads;

    protected static string|\BackedEnum|null $navigationIcon  = 'heroicon-o-arrow-up-tray';
    protected static string|\UnitEnum|null   $navigationGroup = 'Prestasi & Ekskul';
    protected static ?string                 $navigationLabel = 'Import Ekstra CSV';
    protected static ?string                 $slug            = 'import-extracurricular';
    protected static ?int                    $navigationSort  = 11;

    public static function canAccess(): bool
    {
        return auth()->user()?->role === 'admin' || auth()->user()?->role === 'superadmin';
    }

    protected string $view = 'filament.pages.import-extracurricular';

    public ?array $data = [];
    public bool $isParsed = false;
    public array $previewItems = [];
    public array $teachersList = [];
    public array $studentsList = [];

    public function mount(): void
    {
        $this->teachersList = User::where('role', 'guru')->orderBy('name')->pluck('name', 'id')->toArray();
        $this->studentsList = User::where('role', 'siswa')->orderBy('name')->pluck('name', 'id')->toArray();

        // Default parse if file exists
        $defaultFile = public_path('ekstra.csv');
        if (file_exists($defaultFile)) {
            $this->parseFilePath($defaultFile);
        }
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                FileUpload::make('file')
                    ->label('File CSV Ekstrakurikuler (.csv)')
                    ->helperText('Unggah file CSV baru atau gunakan file default `public/ekstra.csv` di bawah.')
                    ->acceptedFileTypes(['text/csv', 'text/plain', 'application/csv'])
                    ->maxSize(10240)
                    ->storeFiles(false),
            ])
            ->statePath('data');
    }

    public function loadDefaultFile(): void
    {
        $defaultFile = public_path('ekstra.csv');
        if (! file_exists($defaultFile)) {
            Notification::make()->title('File ekstra.csv tidak ditemukan di folder public.')->danger()->send();
            return;
        }

        $this->parseFilePath($defaultFile);
        Notification::make()->title('Berhasil memuat data dari public/ekstra.csv!')->success()->send();
    }

    public function startParsing(): void
    {
        $state = $this->form->getState();
        if (empty($state['file'])) {
            Notification::make()->title('Pilih file CSV terlebih dahulu.')->warning()->send();
            return;
        }

        $uploadedFile = $state['file'];
        $filePath = is_string($uploadedFile) ? $uploadedFile : $uploadedFile->getRealPath();

        $this->parseFilePath($filePath);
        Notification::make()->title('File CSV berhasil diproses!')->success()->send();
    }

    private function parseFilePath(string $path): void
    {
        $parsed = ExtracurricularImportService::parseCsv($path);

        $items = [];
        foreach ($parsed as $p) {
            $teacherIds = [];
            foreach ($p['pembinas'] as $t) {
                if ($t['teacher_id']) {
                    $teacherIds[] = (int) $t['teacher_id'];
                }
            }

            $items[] = [
                'name'           => $p['name'],
                'contact_person' => $p['contact_person'],
                'pembinas_raw'   => array_column($p['pembinas'], 'raw_name'),
                'teacher_ids'    => array_values(array_unique($teacherIds)),
                'ketua_raw'      => $p['ketua']['raw_name'] ?? null,
                'ketua_id'       => ! empty($p['ketua']['student_id']) ? (int) $p['ketua']['student_id'] : null,
                'wakil_raw'      => $p['wakil_ketua']['raw_name'] ?? null,
                'wakil_ketua_id' => ! empty($p['wakil_ketua']['student_id']) ? (int) $p['wakil_ketua']['student_id'] : null,
            ];
        }

        $this->previewItems = $items;
        $this->isParsed     = count($items) > 0;
    }

    public function saveAll(): void
    {
        if (empty($this->previewItems)) {
            Notification::make()->title('Tidak ada data ekstra untuk disimpan.')->danger()->send();
            return;
        }

        DB::transaction(function () {
            foreach ($this->previewItems as $item) {
                if (empty($item['name'])) {
                    continue;
                }

                $teacherIds     = array_filter(array_map('intval', $item['teacher_ids'] ?? []));
                $firstTeacherId = $teacherIds[0] ?? null;

                $extra = Extracurricular::updateOrCreate(
                    ['name' => trim($item['name'])],
                    [
                        'contact_person' => $item['contact_person'] ?? null,
                        'pembina_id'     => $firstTeacherId,
                    ]
                );

                // Sync Teachers (Pembina)
                $extra->teachers()->sync($teacherIds);

                // Sync Students (Ketua & Wakil Ketua)
                $studentSync = [];
                if (! empty($item['ketua_id'])) {
                    $studentSync[$item['ketua_id']] = ['role' => 'ketua'];
                }
                if (! empty($item['wakil_ketua_id'])) {
                    $studentSync[$item['wakil_ketua_id']] = ['role' => 'wakil_ketua'];
                }
                $extra->students()->sync($studentSync);
            }
        });

        Notification::make()
            ->title('Data Ekstrakurikuler, Pembina, Ketua, & Wakil Ketua Berhasil Disimpan!')
            ->success()
            ->send();

        $this->redirect('/admin/extracurriculars');
    }
}
