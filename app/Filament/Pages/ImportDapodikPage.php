<?php

namespace App\Filament\Pages;

use App\Exports\SiswaEmailExport;
use App\Imports\DapodikImport;
use App\Models\SchoolClass;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ImportDapodikPage extends Page
{
    protected static string|\BackedEnum|null $navigationIcon  = 'heroicon-o-arrow-up-tray';
    protected static string|\UnitEnum|null  $navigationGroup = 'Manajemen User';
    protected static ?string                $navigationLabel = 'Import Data Siswa';
    protected static ?int                   $navigationSort  = 5;

    public static function canAccess(): bool { return auth()->user()?->role === 'admin'; }

    protected string $view = 'filament.pages.import-dapodik';

    // ── Form & result state ───────────────────────────────────────────────────
    public ?array $data    = [];
    public ?array $results = null;

    // ── Progress state ────────────────────────────────────────────────────────
    public bool   $processing    = false;
    public int    $totalRows     = 0;
    public int    $processedRows = 0;
    public string $tempDataPath  = '';

    // Accumulated counts during chunked processing
    public int   $createdCount  = 0;
    public int   $updatedCount  = 0;
    public int   $skippedCount  = 0;
    public array $errorsList    = [];
    public array $warningsList  = [];

    private const CHUNK_SIZE = 20;

    // ── Header Actions ───────────────────────────────────────────────────────

    protected function getHeaderActions(): array
    {
        return [
            Action::make('download_emails')
                ->label('Download Email Siswa (Excel)')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->modalHeading('Download Rekap Email Siswa')
                ->modalDescription('Unduh data email semua siswa dalam format Excel (.xlsx). Anda dapat memilih seluruh kelas atau memfilter per kelas dan status pengisian email.')
                ->modalSubmitActionLabel('Download File Excel')
                ->form([
                    Select::make('class_id')
                        ->label('Filter Kelas')
                        ->placeholder('Semua Kelas (Seluruh Siswa)')
                        ->options(SchoolClass::orderBy('grade')->orderBy('name')->pluck('name', 'id'))
                        ->searchable()
                        ->preload(),
                    Select::make('status')
                        ->label('Status Email Siswa')
                        ->options([
                            'all'           => 'Semua Siswa (Termasuk yang belum ada email)',
                            'with_email'    => 'Hanya Siswa yang Sudah Ada Email',
                            'without_email' => 'Hanya Siswa yang Belum Mengisi Email',
                        ])
                        ->default('all')
                        ->required(),
                ])
                ->action(function (array $data): BinaryFileResponse {
                    $classId = ! empty($data['class_id']) ? (int) $data['class_id'] : null;
                    $status  = $data['status'] ?? 'all';

                    $className = $classId ? (SchoolClass::find($classId)?->name ?? 'kelas-' . $classId) : 'semua-kelas';
                    $cleanClassName = strtolower(preg_replace('/[^A-Za-z0-9]+/', '-', $className));
                    $filename = 'email-siswa-' . $cleanClassName . '-' . date('Ymd-His') . '.xlsx';

                    return Excel::download(
                        new SiswaEmailExport($classId, $status),
                        $filename
                    );
                }),
        ];
    }

    public function downloadAllEmails(): BinaryFileResponse
    {
        return Excel::download(
            new SiswaEmailExport(),
            'email-semua-siswa-sman1-gianyar-' . date('Ymd-His') . '.xlsx'
        );
    }

    // ── Form ──────────────────────────────────────────────────────────────────

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                FileUpload::make('file')
                    ->label('File Excel Data Siswa / Dapodik')
                    ->helperText('Unggah file Excel Data Siswa (.xlsx / .xls). NISN digunakan sebagai acuan update data siswa.')
                    ->acceptedFileTypes([
                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        'application/vnd.ms-excel',
                    ])
                    ->maxSize(10240)
                    ->required()
                    ->storeFiles(false),
            ])
            ->statePath('data');
    }

    // ── Step 1: Read file, detect header, save temp data ─────────────────────

    public function startImport(): void
    {
        $data         = $this->form->getState();
        $uploadedFile = $data['file'] ?? null;

        if (! $uploadedFile) {
            Notification::make()->title('File tidak ditemukan')->danger()->send();
            return;
        }

        // Load all rows via a collector import
        $collector = new class extends DapodikImport {
            public ?\Illuminate\Support\Collection $allRows = null;
            public function collection(\Illuminate\Support\Collection $rows): void
            {
                $this->allRows = $rows;
            }
        };
        Excel::import($collector, $uploadedFile);

        if (! $collector->allRows || $collector->allRows->isEmpty()) {
            Notification::make()->title('File kosong atau tidak bisa dibaca')->danger()->send();
            return;
        }

        // Find header row (contains 'nisn')
        $headerIndex = null;
        foreach ($collector->allRows->take(15) as $i => $row) {
            $normalized = $row->map(fn($v) => $collector->normalizeKey((string) $v));
            if ($normalized->contains('nisn') || $normalized->contains('nonisn')) {
                $headerIndex = $i;
                break;
            }
        }

        if ($headerIndex === null) {
            Notification::make()
                ->title('Kolom NISN tidak ditemukan')
                ->body('Pastikan file adalah ekspor Data Peserta Didik dari Dapodik.')
                ->danger()
                ->send();
            return;
        }

        $headerRow = $collector->allRows[$headerIndex]->toArray();
        $dataRows  = $collector->allRows->slice($headerIndex + 1)->values()
            ->map(fn($row) => $row->toArray())
            ->toArray();

        // Persist rows to temp JSON (keyed per user to avoid collisions)
        $tempDir  = storage_path('app/temp');
        if (! is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }
        $tempPath = $tempDir . '/dapodik_' . auth()->id() . '.json';
        file_put_contents($tempPath, json_encode(['header' => $headerRow, 'rows' => $dataRows]));

        // Reset state
        $this->tempDataPath  = $tempPath;
        $this->totalRows     = count($dataRows);
        $this->processedRows = 0;
        $this->processing    = true;
        $this->createdCount  = 0;
        $this->updatedCount  = 0;
        $this->skippedCount  = 0;
        $this->errorsList    = [];
        $this->warningsList  = [];
        $this->results       = null;

        $this->form->fill([]);
    }

    // ── Step 2: Process one chunk (triggered by wire:poll) ────────────────────

    public function processChunk(): void
    {
        if (! $this->processing || ! $this->tempDataPath || ! file_exists($this->tempDataPath)) {
            return;
        }

        $stored    = json_decode(file_get_contents($this->tempDataPath), true);
        $headerRow = $stored['header'];
        $allRows   = $stored['rows'];

        $importer           = new DapodikImport();
        $importer->created  = $this->createdCount;
        $importer->updated  = $this->updatedCount;
        $importer->skipped  = $this->skippedCount;
        $importer->errors   = $this->errorsList;
        $importer->warnings = $this->warningsList;

        $colMap = $importer->buildColMap($headerRow);
        $chunk  = array_slice($allRows, $this->processedRows, self::CHUNK_SIZE);

        foreach ($chunk as $offset => $rowData) {
            $lineNum = $this->processedRows + $offset + 2;
            $importer->processRow(collect($rowData), $colMap, $lineNum);
        }

        $this->processedRows += count($chunk);
        $this->createdCount  = $importer->created;
        $this->updatedCount  = $importer->updated;
        $this->skippedCount  = $importer->skipped;
        $this->errorsList    = $importer->errors;
        $this->warningsList  = $importer->warnings;

        if ($this->processedRows >= $this->totalRows || empty($chunk)) {
            $this->finishImport();
        }
    }

    // ── Step 3: Finalise ──────────────────────────────────────────────────────

    private function finishImport(): void
    {
        $this->processing = false;

        if ($this->tempDataPath && file_exists($this->tempDataPath)) {
            @unlink($this->tempDataPath);
            $this->tempDataPath = '';
        }

        $this->results = [
            'created'  => $this->createdCount,
            'updated'  => $this->updatedCount,
            'skipped'  => $this->skippedCount,
            'errors'   => $this->errorsList,
            'warnings' => $this->warningsList,
        ];

        $total = $this->createdCount + $this->updatedCount;

        if ($this->errorsList && $total === 0) {
            Notification::make()->title('Import gagal')->body($this->errorsList[0])->danger()->send();
        } elseif ($total > 0) {
            Notification::make()
                ->title("Import selesai: {$this->createdCount} ditambah, {$this->updatedCount} diperbarui")
                ->success()
                ->send();
        } else {
            Notification::make()->title('Tidak ada data yang diproses')->warning()->send();
        }
    }
}
