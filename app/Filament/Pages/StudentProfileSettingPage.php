<?php

namespace App\Filament\Pages;

use App\Filament\Support\AdminAccess;
use App\Models\AppSetting;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Section;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;

class StudentProfileSettingPage extends Page
{
    protected static string|\BackedEnum|null $navigationIcon  = 'heroicon-o-lock-closed';
    protected static string|\UnitEnum|null   $navigationGroup = 'Manajemen User';
    protected static ?string                 $navigationLabel = 'Pengaturan Kunci Profil Siswa';
    protected static ?int                    $navigationSort  = 90;

    public static function canAccess(): bool { return AdminAccess::can('Manajemen User'); }

    protected string $view = 'filament.pages.student-profile-setting';

    public ?array $data = [];

    public function mount(): void
    {
        $this->data = [
            'allow_student_profile_edit' => AppSetting::canStudentEditProfile(),
        ];
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Kontrol Akses Pengisian Data Profil Siswa')
                    ->description('Atur apakah siswa diizinkan mengisi & memperbarui data profil mandiri via Web dan Aplikasi Mobile.')
                    ->schema([
                        Toggle::make('allow_student_profile_edit')
                            ->label('🔓 Izinkan Siswa Mengisi & Edit Profil (Buka)')
                            ->helperText('Jika diaktifkan (Buka), siswa bisa mengisi data domisili, ortu, dan kesehatan. Jika dinonaktifkan (Kunci/Read-Only), form di Web & Mobile berubah menjadi read-only.')
                            ->default(true),
                    ]),
            ]);
    }

    public function save(): void
    {
        $state = $this->data['allow_student_profile_edit'] ?? true;
        AppSetting::set('allow_student_profile_edit', (bool) $state);

        Notification::make()
            ->title('Pengaturan Berhasil Disimpan')
            ->body($state ? 'Pengisian profil siswa dibuka (Mode Edit).' : 'Pengisian profil siswa dikunci (Mode Read-Only).')
            ->success()
            ->send();
    }
}
