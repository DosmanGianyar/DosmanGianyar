<?php

namespace App\Filament\Resources\StudentAchievementResource\Pages;

use App\Filament\Resources\StudentAchievementResource;
use App\Filament\Resources\UserResource;
use App\Models\AchievementCategory;
use App\Models\StudentAchievement;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;

class ViewStudentAchievement extends ViewRecord
{
    protected static string $resource = StudentAchievementResource::class;

    public function getTitle(): string
    {
        return 'Detail & Verifikasi Prestasi Siswa';
    }

    public function getSubheading(): ?string
    {
        /** @var StudentAchievement $record */
        $record = $this->getRecord();
        if (! $record) return null;

        $studentName = $record->student?->name ?? 'Siswa';
        $className = $record->student?->schoolClass?->name ? "Kelas {$record->student->schoolClass->name}" : '';
        $type = $record->isBeregu() ? '👥 Prestasi Beregu' : '👤 Prestasi Individu';
        $rank = $record->rank ? " • 🏆 {$record->rank}" : '';
        $level = $record->levelLabel() ? " (Tingkat {$record->levelLabel()})" : '';

        return "{$studentName}" . ($className ? " ({$className})" : '') . " • {$type}{$rank}{$level}";
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('back')
                ->label('Kembali ke Daftar')
                ->icon('heroicon-o-arrow-left')
                ->color('gray')
                ->outlined()
                ->url(StudentAchievementResource::getUrl('index')),

            Action::make('edit_achievement')
                ->label('Edit Lengkap (Form)')
                ->tooltip('Ubah seluruh data ajuan & berkas melalui form modal 4 tab')
                ->icon('heroicon-o-pencil-square')
                ->color('primary')
                ->outlined()
                ->modalHeading('Edit Lengkap Data & Berkas Prestasi Siswa')
                ->modalDescription('Verifikator/Admin dapat mengedit seluruh data prestasi, mengganti siswa utama, mengunggah/mengganti berkas, serta memperbarui kurasi tanpa perlu berpindah halaman.')
                ->modalSubmitActionLabel('Simpan Semua Perubahan')
                ->modalWidth('5xl')
                ->fillForm(fn (StudentAchievement $record): array => [
                    'student_id'                 => $record->student_id,
                    'category_id'                => $record->category_id,
                    'field_category'             => $record->field_category,
                    'participation_type'         => $record->participation_type,
                    'level'                      => $record->level,
                    'title'                      => $record->title,
                    'event_name'                 => $record->event_name,
                    'organizer'                  => $record->organizer,
                    'rank'                       => $record->rank,
                    'achievement_date'           => $record->achievement_date?->format('Y-m-d'),
                    'event_url'                  => $record->event_url,
                    'description'                => $record->description,
                    'certificate'                => $record->certificate,
                    'assignment_letter'          => $record->assignment_letter,
                    'photo'                      => $record->photo,
                    'is_curation'                => (bool) $record->is_curation,
                    'doc_standard_file'          => $record->doc_standard_file,
                    'doc_standard_url'           => $record->doc_standard_url,
                    'selection_level_file'       => $record->selection_level_file,
                    'selection_level'            => $record->selection_level,
                    'selection_level_url'        => $record->selection_level_url,
                    'frequency_consistency_file' => $record->frequency_consistency_file,
                    'frequency_consistency'      => $record->frequency_consistency,
                    'infrastructure_file'        => $record->infrastructure_file,
                    'infrastructure_type'        => $record->infrastructure_type,
                    'reward_recap_file'          => $record->reward_recap_file,
                    'reward_certificate_file'    => $record->reward_certificate_file,
                    'reward_photo_file'          => $record->reward_photo_file,
                    'status'                     => $record->status,
                    'curation_status'            => $record->curation_status,
                    'curation_note'              => $record->curation_note,
                ])
                ->form([
                    Tabs::make('EditPrestasiTabs')
                        ->tabs([
                            Tab::make('1. Informasi Lomba & Siswa')
                                ->icon('heroicon-o-trophy')
                                ->schema([
                                    Select::make('student_id')
                                        ->label('Siswa Utama / Pemilik Prestasi')
                                        ->options(User::where('role', 'siswa')->orderBy('name')->pluck('name', 'id'))
                                        ->searchable()
                                        ->preload()
                                        ->required(),

                                    Select::make('category_id')
                                        ->label('Kategori Prestasi')
                                        ->options(AchievementCategory::pluck('name', 'id'))
                                        ->required(),

                                    Select::make('field_category')
                                        ->label('Bidang / Rumpun Talenta')
                                        ->options([
                                            'akademik'     => 'Akademik',
                                            'sains_riset'  => 'Sains & Riset',
                                            'olahraga'     => 'Olahraga',
                                            'seni_budaya'  => 'Seni & Budaya',
                                            'bahasa_debat' => 'Bahasa & Debat',
                                            'keagamaan'    => 'Keagamaan',
                                            'lainnya'      => 'Lainnya',
                                        ])
                                        ->required(),

                                    Select::make('participation_type')
                                        ->label('Jenis Keikutsertaan')
                                        ->options([
                                            'individu' => 'Perorangan (Individu)',
                                            'beregu'   => 'Beregu (Kelompok)',
                                        ])
                                        ->required(),

                                    Select::make('level')
                                        ->label('Tingkat Kejuaraan')
                                        ->options([
                                            'sekolah'       => 'Sekolah',
                                            'kabupaten'     => 'Kabupaten/Kota',
                                            'provinsi'      => 'Provinsi',
                                            'nasional'      => 'Nasional',
                                            'internasional' => 'Internasional',
                                        ])
                                        ->required(),

                                    TextInput::make('title')
                                        ->label('Judul Prestasi / Kejuaraan')
                                        ->required()
                                        ->maxLength(200)
                                        ->columnSpanFull(),

                                    TextInput::make('event_name')
                                        ->label('Nama Ajang / Event')
                                        ->maxLength(200),

                                    TextInput::make('organizer')
                                        ->label('Penyelenggara Lomba')
                                        ->maxLength(200),

                                    TextInput::make('rank')
                                        ->label('Peringkat / Juara')
                                        ->placeholder('Contoh: Juara 1 / Medali Emas')
                                        ->maxLength(50),

                                    DatePicker::make('achievement_date')
                                        ->label('Tanggal Lomba / Capaian')
                                        ->required(),

                                    TextInput::make('event_url')
                                        ->label('Website Resmi Ajang / Berita (URL)')
                                        ->url()
                                        ->maxLength(500)
                                        ->columnSpanFull(),

                                    Textarea::make('description')
                                        ->label('Deskripsi / Catatan Tambahan Siswa')
                                        ->columnSpanFull()
                                        ->rows(2),
                                ])
                                ->columns(2),

                            Tab::make('2. Berkas Tagihan Utama (#11-#13)')
                                ->icon('heroicon-o-document-arrow-up')
                                ->schema([
                                    FileUpload::make('certificate')
                                        ->label('Tagihan #11: Scan Piagam / Sertifikat (Khusus PDF)')
                                        ->disk('public')
                                        ->acceptedFileTypes(['application/pdf'])
                                        ->directory('achievements/certificates')
                                        ->maxSize(10240),

                                    FileUpload::make('assignment_letter')
                                        ->label('Tagihan #12: Surat Tugas / Rekomendasi (Khusus PDF)')
                                        ->disk('public')
                                        ->acceptedFileTypes(['application/pdf'])
                                        ->directory('achievements/letters')
                                        ->maxSize(10240),

                                    FileUpload::make('photo')
                                        ->label('Tagihan #13: Foto Kegiatan / Penyerahan Piagam')
                                        ->disk('public')
                                        ->image()
                                        ->directory('achievements/photos')
                                        ->maxSize(5120),
                                ])
                                ->columns(3),

                            Tab::make('3. Berkas 5 Poin Kurasi (#14-#18)')
                                ->icon('heroicon-o-academic-cap')
                                ->schema([
                                    Toggle::make('is_curation')
                                        ->label('Daftarkan Sebagai Prestasi Kurasi Resmi Puspresnas / Kemendikdasmen')
                                        ->columnSpanFull(),

                                    FileUpload::make('doc_standard_file')
                                        ->label('Tagihan #14: P1 Juknis / Pedoman Lomba (PDF)')
                                        ->disk('public')
                                        ->acceptedFileTypes(['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'])
                                        ->directory('curations/doc_standards')
                                        ->maxSize(10240),

                                    TextInput::make('doc_standard_url')
                                        ->label('P1: URL Pedoman / Juknis Online')
                                        ->url(),

                                    FileUpload::make('selection_level_file')
                                        ->label('Tagihan #15: P2 Bukti Jenjang Seleksi (PDF)')
                                        ->disk('public')
                                        ->acceptedFileTypes(['application/pdf'])
                                        ->directory('curations/selection_levels')
                                        ->maxSize(10240),

                                    Select::make('selection_level')
                                        ->label('P2: Tahapan Seleksi')
                                        ->options([
                                            'sekolah'       => 'Seleksi Tingkat Sekolah',
                                            'kabupaten'     => 'Seleksi Tingkat Kab/Kota',
                                            'provinsi'      => 'Seleksi Tingkat Provinsi',
                                            'langsung'      => 'Tanpa Seleksi / Langsung Nasional',
                                            'internasional' => 'Seleksi Nasional Menuju Internasional',
                                        ]),

                                    FileUpload::make('frequency_consistency_file')
                                        ->label('Tagihan #16: P3 Bukti Konsistensi Lintas Tahun (PDF)')
                                        ->disk('public')
                                        ->acceptedFileTypes(['application/pdf'])
                                        ->directory('curations/frequencies')
                                        ->maxSize(20480),

                                    Select::make('frequency_consistency')
                                        ->label('P3: Frekuensi Penyelenggaraan')
                                        ->options([
                                            'tahunan'     => 'Rutin Tahunan',
                                            'dua_tahunan' => 'Dua Tahunan (Biennial)',
                                            'insidental'  => 'Insidental / Pertama Kali',
                                        ]),

                                    FileUpload::make('infrastructure_file')
                                        ->label('Tagihan #17: P4 Dokumentasi Sarpras/Venue')
                                        ->disk('public')
                                        ->directory('curations/infrastructures')
                                        ->maxSize(10240),

                                    Select::make('infrastructure_type')
                                        ->label('P4: Kelayakan Sarpras')
                                        ->options([
                                            'standar'  => 'Venue / Sarpras Standar Resmi Nasional',
                                            'memadai'  => 'Sarpras Memadai',
                                            'terbatas' => 'Sarpras Terbatas / Sederhana',
                                        ]),

                                    FileUpload::make('reward_recap_file')
                                        ->label('Tagihan #18: P5 Rekap Juara / SK Pemenang')
                                        ->disk('public')
                                        ->acceptedFileTypes(['application/pdf', 'image/*'])
                                        ->directory('curations/rewards/recaps')
                                        ->maxSize(10240),

                                    FileUpload::make('reward_certificate_file')
                                        ->label('P5: Sertifikat Penghargaan (Khusus PDF)')
                                        ->disk('public')
                                        ->acceptedFileTypes(['application/pdf'])
                                        ->directory('curations/rewards/certificates')
                                        ->maxSize(10240),
                                ])
                                ->columns(2),

                            Tab::make('4. Status Verifikasi & Kurasi')
                                ->icon('heroicon-o-check-badge')
                                ->schema([
                                    Select::make('status')
                                        ->label('Status Verifikasi')
                                        ->options([
                                            'pending'  => '⏳ Menunggu Verifikasi',
                                            'approved' => '✅ Disetujui / Valid (Masuk Rekap Sekolah)',
                                            'rejected' => '❌ Ditolak / Tidak Valid',
                                        ])
                                        ->required(),

                                    Select::make('curation_status')
                                        ->label('Kategori Pendataan Kurasi')
                                        ->options([
                                            'pending'       => 'Menunggu Penilaian',
                                            'curated'       => 'Prestasi Kurasi Resmi (SIMT/Puspresnas)',
                                            'not_curatable' => 'Prestasi Internal Sekolah',
                                            'revision'      => 'Perlu Revisi Berkas',
                                            'rejected'      => 'Tidak Layak / Ditolak',
                                        ])
                                        ->required(),

                                    Textarea::make('curation_note')
                                        ->label('Catatan Verifikasi / Petunjuk Revisi / Alasan Penolakan')
                                        ->placeholder('Isi catatan internal atau petunjuk revisi untuk siswa')
                                        ->columnSpanFull()
                                        ->rows(3),
                                ])
                                ->columns(2),
                        ]),
                ])
                ->action(function (StudentAchievement $record, array $data): void {
                    if ($record->isBeregu() && ! empty($record->team_code)) {
                        $teamData = $data;
                        unset($teamData['student_id']);
                        StudentAchievement::where('team_code', $record->team_code)->update($teamData);
                        $record->update(['student_id' => $data['student_id']]);
                    } else {
                        $record->update($data);
                    }
                    Notification::make()->title('Data & Berkas Prestasi Berhasil Diperbarui')->success()->send();
                }),

            // ─── ACTION EDIT PER-BARIS (QUICK EDIT MODALS - HIDDEN FROM HEADER) ──
            Action::make('edit_event_name')
                ->label('Edit Nama Ajang / Event')
                ->extraAttributes(['style' => 'display: none !important'])
                ->modalHeading('Edit Nama Ajang / Event Lomba')
                ->modalDescription('Lengkapi atau ubah nama ajang kejuaraan/cabang olahraga (contoh: Kejuaraan Taekwondo Walikota Cup Denpasar XVI 2026).')
                ->modalSubmitActionLabel('Simpan Nama Ajang')
                ->fillForm(fn (StudentAchievement $record): array => ['event_name' => $record->event_name])
                ->form([
                    TextInput::make('event_name')
                        ->label('Nama Ajang / Event Perlombaan')
                        ->placeholder('Contoh: Kejuaraan Taekwondo Walikota Cup XVI 2026')
                        ->required()
                        ->maxLength(200),
                ])
                ->action(function (StudentAchievement $record, array $data): void {
                    if ($record->isBeregu() && ! empty($record->team_code)) {
                        StudentAchievement::where('team_code', $record->team_code)->update(['event_name' => $data['event_name']]);
                    } else {
                        $record->update(['event_name' => $data['event_name']]);
                    }
                    Notification::make()->title('Nama ajang lomba berhasil diperbarui')->success()->send();
                }),

            Action::make('edit_title')
                ->label('Edit Judul Prestasi')
                ->extraAttributes(['style' => 'display: none !important'])
                ->modalHeading('Edit Judul Prestasi / Kejuaraan')
                ->modalDescription('Ubah judul capaian prestasi yang diraih oleh siswa.')
                ->modalSubmitActionLabel('Simpan Judul')
                ->fillForm(fn (StudentAchievement $record): array => ['title' => $record->title])
                ->form([
                    TextInput::make('title')
                        ->label('Judul Prestasi / Kejuaraan')
                        ->placeholder('Contoh: JUARA 3 WALIKOTA CUP DENPASAR XVI 2026')
                        ->required()
                        ->maxLength(200),
                ])
                ->action(function (StudentAchievement $record, array $data): void {
                    if ($record->isBeregu() && ! empty($record->team_code)) {
                        StudentAchievement::where('team_code', $record->team_code)->update(['title' => $data['title']]);
                    } else {
                        $record->update(['title' => $data['title']]);
                    }
                    Notification::make()->title('Judul prestasi berhasil diperbarui')->success()->send();
                }),

            Action::make('edit_organizer')
                ->label('Edit Penyelenggara Lomba')
                ->extraAttributes(['style' => 'display: none !important'])
                ->modalHeading('Edit Penyelenggara Lomba')
                ->modalDescription('Ubah instansi, induk organisasi, atau lembaga penyelenggara ajang.')
                ->modalSubmitActionLabel('Simpan Penyelenggara')
                ->fillForm(fn (StudentAchievement $record): array => ['organizer' => $record->organizer])
                ->form([
                    TextInput::make('organizer')
                        ->label('Penyelenggara Lomba')
                        ->placeholder('Contoh: KONI Kota Denpasar / Pengprov Taekwondo Indonesia')
                        ->required()
                        ->maxLength(200),
                ])
                ->action(function (StudentAchievement $record, array $data): void {
                    if ($record->isBeregu() && ! empty($record->team_code)) {
                        StudentAchievement::where('team_code', $record->team_code)->update(['organizer' => $data['organizer']]);
                    } else {
                        $record->update(['organizer' => $data['organizer']]);
                    }
                    Notification::make()->title('Penyelenggara lomba berhasil diperbarui')->success()->send();
                }),

            Action::make('edit_level')
                ->label('Edit Tingkat Kejuaraan')
                ->extraAttributes(['style' => 'display: none !important'])
                ->modalHeading('Edit Tingkat Kejuaraan')
                ->modalSubmitActionLabel('Simpan Tingkat')
                ->fillForm(fn (StudentAchievement $record): array => ['level' => $record->level])
                ->form([
                    Select::make('level')
                        ->label('Tingkat Kejuaraan')
                        ->options([
                            'sekolah'       => 'Sekolah (Internal)',
                            'kabupaten'     => 'Kabupaten / Kota',
                            'provinsi'      => 'Provinsi',
                            'nasional'      => 'Nasional',
                            'internasional' => 'Internasional',
                        ])
                        ->required(),
                ])
                ->action(function (StudentAchievement $record, array $data): void {
                    if ($record->isBeregu() && ! empty($record->team_code)) {
                        StudentAchievement::where('team_code', $record->team_code)->update(['level' => $data['level']]);
                    } else {
                        $record->update(['level' => $data['level']]);
                    }
                    Notification::make()->title('Tingkat kejuaraan berhasil diperbarui')->success()->send();
                }),

            Action::make('edit_rank')
                ->label('Edit Peringkat / Capaian')
                ->extraAttributes(['style' => 'display: none !important'])
                ->modalHeading('Edit Peringkat / Capaian Juara')
                ->modalSubmitActionLabel('Simpan Peringkat')
                ->fillForm(fn (StudentAchievement $record): array => ['rank' => $record->rank])
                ->form([
                    TextInput::make('rank')
                        ->label('Peringkat / Capaian Juara')
                        ->placeholder('Contoh: JUARA 3, Medali Perunggu, Harapan 1')
                        ->required()
                        ->maxLength(100),
                ])
                ->action(function (StudentAchievement $record, array $data): void {
                    if ($record->isBeregu() && ! empty($record->team_code)) {
                        StudentAchievement::where('team_code', $record->team_code)->update(['rank' => $data['rank']]);
                    } else {
                        $record->update(['rank' => $data['rank']]);
                    }
                    Notification::make()->title('Peringkat juara berhasil diperbarui')->success()->send();
                }),

            Action::make('edit_field_category')
                ->label('Edit Rumpun Talenta & Kategori')
                ->extraAttributes(['style' => 'display: none !important'])
                ->modalHeading('Edit Rumpun Bidang & Kategori Prestasi')
                ->modalSubmitActionLabel('Simpan Kategori')
                ->fillForm(fn (StudentAchievement $record): array => [
                    'field_category' => $record->field_category,
                    'category_id'    => $record->category_id,
                ])
                ->form([
                    Select::make('field_category')
                        ->label('Rumpun Talenta')
                        ->options([
                            'sains_riset'  => 'Sains & Riset',
                            'olahraga'     => 'Olahraga',
                            'seni_budaya'  => 'Seni & Budaya',
                            'bahasa_debat' => 'Bahasa & Debat',
                            'keagamaan'    => 'Keagamaan',
                            'akademik'     => 'Akademik',
                            'lainnya'      => 'Lainnya',
                        ])
                        ->required(),
                    Select::make('category_id')
                        ->label('Kategori Prestasi')
                        ->options(AchievementCategory::orderBy('name')->pluck('name', 'id'))
                        ->searchable()
                        ->required(),
                ])
                ->action(function (StudentAchievement $record, array $data): void {
                    if ($record->isBeregu() && ! empty($record->team_code)) {
                        StudentAchievement::where('team_code', $record->team_code)->update([
                            'field_category' => $data['field_category'],
                            'category_id'    => $data['category_id'],
                        ]);
                    } else {
                        $record->update([
                            'field_category' => $data['field_category'],
                            'category_id'    => $data['category_id'],
                        ]);
                    }
                    Notification::make()->title('Rumpun bidang & kategori berhasil diperbarui')->success()->send();
                }),

            Action::make('edit_participation_type')
                ->label('Edit Jenis Partisipasi')
                ->extraAttributes(['style' => 'display: none !important'])
                ->modalHeading('Edit Jenis Partisipasi')
                ->modalSubmitActionLabel('Simpan Partisipasi')
                ->fillForm(fn (StudentAchievement $record): array => ['participation_type' => $record->participation_type])
                ->form([
                    Select::make('participation_type')
                        ->label('Jenis Partisipasi')
                        ->options([
                            'individu' => 'Perorangan (Individu)',
                            'beregu'   => 'Beregu / Kelompok',
                        ])
                        ->required(),
                ])
                ->action(function (StudentAchievement $record, array $data): void {
                    $record->update(['participation_type' => $data['participation_type']]);
                    Notification::make()->title('Jenis partisipasi berhasil diperbarui')->success()->send();
                }),

            Action::make('edit_achievement_date')
                ->label('Edit Tanggal Lomba')
                ->extraAttributes(['style' => 'display: none !important'])
                ->modalHeading('Edit Tanggal Pelaksanaan Lomba')
                ->modalSubmitActionLabel('Simpan Tanggal')
                ->fillForm(fn (StudentAchievement $record): array => ['achievement_date' => $record->achievement_date?->format('Y-m-d')])
                ->form([
                    DatePicker::make('achievement_date')
                        ->label('Tanggal Pelaksanaan / Capaian')
                        ->required(),
                ])
                ->action(function (StudentAchievement $record, array $data): void {
                    if ($record->isBeregu() && ! empty($record->team_code)) {
                        StudentAchievement::where('team_code', $record->team_code)->update(['achievement_date' => $data['achievement_date']]);
                    } else {
                        $record->update(['achievement_date' => $data['achievement_date']]);
                    }
                    Notification::make()->title('Tanggal lomba berhasil diperbarui')->success()->send();
                }),

            Action::make('edit_event_url')
                ->label('Edit Website Resmi')
                ->extraAttributes(['style' => 'display: none !important'])
                ->modalHeading('Edit Website Resmi Ajang / Berita')
                ->modalSubmitActionLabel('Simpan URL')
                ->fillForm(fn (StudentAchievement $record): array => ['event_url' => $record->event_url])
                ->form([
                    TextInput::make('event_url')
                        ->label('Website Resmi Ajang / Tautan Berita')
                        ->placeholder('https://...')
                        ->url()
                        ->maxLength(255),
                ])
                ->action(function (StudentAchievement $record, array $data): void {
                    if ($record->isBeregu() && ! empty($record->team_code)) {
                        StudentAchievement::where('team_code', $record->team_code)->update(['event_url' => $data['event_url']]);
                    } else {
                        $record->update(['event_url' => $data['event_url']]);
                    }
                    Notification::make()->title('Website resmi lomba berhasil diperbarui')->success()->send();
                }),

            Action::make('edit_description')
                ->label('Edit Deskripsi / Catatan')
                ->extraAttributes(['style' => 'display: none !important'])
                ->modalHeading('Edit Deskripsi / Catatan Tambahan')
                ->modalSubmitActionLabel('Simpan Catatan')
                ->fillForm(fn (StudentAchievement $record): array => ['description' => $record->description])
                ->form([
                    Textarea::make('description')
                        ->label('Deskripsi / Catatan Tambahan')
                        ->placeholder('Tuliskan rincian atau keterangan tambahan tentang ajang lomba ini...')
                        ->rows(4),
                ])
                ->action(function (StudentAchievement $record, array $data): void {
                    if ($record->isBeregu() && ! empty($record->team_code)) {
                        StudentAchievement::where('team_code', $record->team_code)->update(['description' => $data['description']]);
                    } else {
                        $record->update(['description' => $data['description']]);
                    }
                    Notification::make()->title('Deskripsi berhasil diperbarui')->success()->send();
                }),

            // Alias edit_files agar tombol yang memanggil mountAction('edit_files') tetap berfungsi
            Action::make('edit_files')
                ->label('Bantu Edit Berkas & Data')
                ->extraAttributes(['style' => 'display: none !important']),

            Action::make('approve_achievement')
                ->label('Setujui / Valid')
                ->tooltip('Setujui & Masukkan ke Rekap Prestasi Sekolah')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Setujui & Sahkan Prestasi')
                ->modalDescription(function (StudentAchievement $record): string {
                    if ($record->isBeregu()) {
                        $count = $record->team_members->count();
                        return "Prestasi beregu ini akan disetujui untuk SELURUH tim ({$count} siswa). Data akan langsung masuk ke Rekapitulasi Sekolah (dihitung 1 Prestasi Sekolah).";
                    }
                    return 'Prestasi siswa ini akan disetujui & disahkan ke Rekapitulasi Prestasi Sekolah.';
                })
                ->action(function (StudentAchievement $record): void {
                    if ($record->isBeregu() && ! empty($record->team_code)) {
                        $affected = StudentAchievement::where('team_code', $record->team_code)->update([
                            'status'          => 'approved',
                            'curation_status' => $record->is_curation ? 'curated' : 'not_curatable',
                            'curation_note'   => null,
                            'verified_by'     => auth()->id(),
                            'verified_at'     => now(),
                        ]);
                    } elseif ($record->isBeregu()) {
                        $affected = StudentAchievement::where('participation_type', 'beregu')
                            ->where('title', $record->title)
                            ->where('achievement_date', $record->achievement_date)
                            ->update([
                                'status'          => 'approved',
                                'curation_status' => $record->is_curation ? 'curated' : 'not_curatable',
                                'curation_note'   => null,
                                'verified_by'     => auth()->id(),
                                'verified_at'     => now(),
                            ]);
                    } else {
                        $record->update([
                            'status'          => 'approved',
                            'curation_status' => $record->is_curation ? 'curated' : 'not_curatable',
                            'curation_note'   => null,
                            'verified_by'     => auth()->id(),
                            'verified_at'     => now(),
                        ]);
                        $affected = 1;
                    }
                    Notification::make()->title("Prestasi Disetujui ({$affected} Siswa)")->success()->send();
                    $this->redirect(StudentAchievementResource::getUrl('index'));
                })
                ->successRedirectUrl(StudentAchievementResource::getUrl('index')),

            Action::make('revision')
                ->label('Minta Revisi')
                ->tooltip('Minta Siswa Memperbaiki Berkas/Data')
                ->icon('heroicon-o-arrow-path')
                ->color('warning')
                ->form([
                    Textarea::make('curation_note')
                        ->label('Catatan Revisi untuk Siswa')
                        ->placeholder('Jelaskan nomor tagihan & berkas yang perlu diperbaiki (contoh: Mohon upload ulang Tagihan #11 Scan Piagam karena buram)')
                        ->required()
                        ->rows(3),
                ])
                ->action(function (StudentAchievement $record, array $data): void {
                    if ($record->isBeregu() && ! empty($record->team_code)) {
                        $affected = StudentAchievement::where('team_code', $record->team_code)->update([
                            'status'          => 'pending',
                            'curation_status' => 'revision',
                            'curation_note'   => $data['curation_note'],
                            'verified_by'     => auth()->id(),
                            'verified_at'     => now(),
                        ]);
                    } else {
                        $record->update([
                            'status'          => 'pending',
                            'curation_status' => 'revision',
                            'curation_note'   => $data['curation_note'],
                            'verified_by'     => auth()->id(),
                            'verified_at'     => now(),
                        ]);
                        $affected = 1;
                    }
                    Notification::make()->title("Diminta Revisi Berkas ({$affected} Siswa)")->warning()->send();
                    $this->redirect(StudentAchievementResource::getUrl('index'));
                })
                ->successRedirectUrl(StudentAchievementResource::getUrl('index')),

            Action::make('reject')
                ->label('Tolak')
                ->tooltip('Tolak Ajuan Prestasi')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->form([
                    Textarea::make('curation_note')
                        ->label('Alasan Penolakan')
                        ->placeholder('Jelaskan alasan penolakan (contoh: Sertifikat tidak valid/bukan atas nama siswa)')
                        ->required()
                        ->rows(3),
                ])
                ->action(function (StudentAchievement $record, array $data): void {
                    if ($record->isBeregu() && ! empty($record->team_code)) {
                        $affected = StudentAchievement::where('team_code', $record->team_code)->update([
                            'status'           => 'rejected',
                            'curation_status'  => 'rejected',
                            'curation_note'    => $data['curation_note'],
                            'rejection_reason' => $data['curation_note'],
                            'verified_by'      => auth()->id(),
                            'verified_at'      => now(),
                        ]);
                    } else {
                        $record->update([
                            'status'           => 'rejected',
                            'curation_status'  => 'rejected',
                            'curation_note'    => $data['curation_note'],
                            'rejection_reason' => $data['curation_note'],
                            'verified_by'      => auth()->id(),
                            'verified_at'      => now(),
                        ]);
                        $affected = 1;
                    }
                    Notification::make()->title("Prestasi Ditolak ({$affected} Siswa)")->danger()->send();
                    $this->redirect(StudentAchievementResource::getUrl('index'));
                })
                ->successRedirectUrl(StudentAchievementResource::getUrl('index')),

            ActionGroup::make([
                Action::make('reset_pending')
                    ->label('Reset Ke Menunggu Verifikasi')
                    ->tooltip('Batalkan penetapan dan kembalikan ke status Menunggu Verifikasi')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('gray')
                    ->requiresConfirmation()
                    ->modalHeading('Batalkan Status Verifikasi')
                    ->modalDescription('Apakah Anda yakin ingin membatalkan status verifikasi ini dan mengembalikannya ke status Menunggu Verifikasi?')
                    ->action(function (StudentAchievement $record): void {
                        if ($record->isBeregu() && ! empty($record->team_code)) {
                            StudentAchievement::where('team_code', $record->team_code)->update([
                                'status'           => 'pending',
                                'curation_status'  => 'pending',
                                'curation_note'    => null,
                                'rejection_reason' => null,
                                'verified_by'      => null,
                                'verified_at'      => null,
                            ]);
                        } else {
                            $record->update([
                                'status'           => 'pending',
                                'curation_status'  => 'pending',
                                'curation_note'    => null,
                                'rejection_reason' => null,
                                'verified_by'      => null,
                                'verified_at'      => null,
                            ]);
                        }
                        Notification::make()->title('Status verifikasi dibatalkan & dikembalikan ke Menunggu Verifikasi')->info()->send();
                        $this->redirect(StudentAchievementResource::getUrl('index'));
                    })
                    ->successRedirectUrl(StudentAchievementResource::getUrl('index')),

                Action::make('student_profile')
                    ->label('Buka Profil Siswa')
                    ->tooltip('Buka Profil Lengkap Siswa')
                    ->icon('heroicon-o-user-circle')
                    ->color('info')
                    ->url(fn (StudentAchievement $record): ?string => $record->student_id ? UserResource::getUrl('view', ['record' => $record->student_id]) : null)
                    ->openUrlInNewTab(),

                DeleteAction::make()
                    ->label('Hapus Ajuan')
                    ->icon('heroicon-o-trash')
                    ->requiresConfirmation()
                    ->modalHeading('Hapus Data Ajuan Prestasi?')
                    ->modalDescription('Apakah Anda yakin ingin menghapus data ajuan prestasi ini secara permanen? Data yang dihapus tidak dapat dikembalikan.')
                    ->successRedirectUrl(StudentAchievementResource::getUrl('index')),
            ])
                ->label('Opsi Lainnya')
                ->icon('heroicon-m-ellipsis-vertical')
                ->color('gray')
                ->button(),
        ];
    }
}
