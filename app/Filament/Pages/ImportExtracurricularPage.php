<?php

namespace App\Filament\Pages;

use App\Models\User;
use App\Services\ExtracurricularImportService;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Session;
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

    public int $currentPage = 1;
    public int $perPage = 20;

    public function getTeachersCollectionProperty()
    {
        return User::where('role', 'guru')->orderBy('name')->get();
    }

    public function getStudentsCollectionProperty()
    {
        return User::where('role', 'siswa')->orderBy('name')->get();
    }

    protected function getSessionKey(): string
    {
        return 'extracurricular_import_items_' . auth()->id();
    }

    public function mount(): void
    {
        @ini_set('memory_limit', '512M');

        if (Session::has($this->getSessionKey())) {
            $this->isParsed = true;
        } else {
            $defaultFile = public_path('ekstra.csv');
            if (file_exists($defaultFile)) {
                $this->parseFilePath($defaultFile);
            }
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
        @ini_set('memory_limit', '512M');

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
                'temp_id'        => $p['temp_id'] ?? uniqid('extra_'),
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

        Session::put($this->getSessionKey(), $items);
        $this->isParsed    = count($items) > 0;
        $this->currentPage = 1;
    }

    public function getAllSessionItems(): array
    {
        return Session::get($this->getSessionKey(), []);
    }

    public function getPaginatedItems(): array
    {
        $all    = $this->getAllSessionItems();
        $offset = ($this->currentPage - 1) * $this->perPage;
        return array_slice($all, $offset, $this->perPage);
    }

    public function getTotalPages(): int
    {
        $count = count($this->getAllSessionItems());
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

    public function updateItemRow(string $tempId, string $field, mixed $value): void
    {
        $items = $this->getAllSessionItems();

        foreach ($items as &$item) {
            if ($item['temp_id'] === $tempId) {
                if ($field === 'add_teacher_id') {
                    $tId = (int) $value;
                    if (! in_array($tId, $item['teacher_ids'])) {
                        $item['teacher_ids'][] = $tId;
                    }
                } elseif ($field === 'remove_teacher_id') {
                    $tId                 = (int) $value;
                    $item['teacher_ids'] = array_values(array_filter($item['teacher_ids'], fn ($id) => (int) $id !== $tId));
                } elseif ($field === 'ketua_id') {
                    $item['ketua_id'] = $value ? (int) $value : null;
                } elseif ($field === 'wakil_ketua_id') {
                    $item['wakil_ketua_id'] = $value ? (int) $value : null;
                } elseif ($field === 'name') {
                    $item['name'] = trim((string) $value);
                } elseif ($field === 'contact_person') {
                    $item['contact_person'] = trim((string) $value);
                }
                break;
            }
        }

        Session::put($this->getSessionKey(), $items);
    }

    public function saveToDatabase(ExtracurricularImportService $service): void
    {
        @ini_set('memory_limit', '512M');
        @ini_set('max_execution_time', '300');

        $items = $this->getAllSessionItems();

        if (empty($items)) {
            Notification::make()->title('Tidak ada data ekstra untuk disimpan.')->warning()->send();
            return;
        }

        try {
            $count = $service->saveExtracurriculars($items);

            Session::forget($this->getSessionKey());
            $this->isParsed = false;
            $this->form->fill();

            Notification::make()
                ->title('Data Ekstrakurikuler Berhasil Disimpan!')
                ->body("Sebanyak {$count} data ekstrakurikuler beserta Pembina & Pengurus telah tersimpan di database.")
                ->success()
                ->persistent()
                ->send();
        } catch (\Throwable $e) {
            Notification::make()
                ->title('Gagal Menyimpan Ekstrakurikuler')
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
