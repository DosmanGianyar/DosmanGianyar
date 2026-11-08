<?php

namespace App\Filament\Pages;

use App\Filament\Support\AdminAccess;
use App\Models\AppSetting;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;

class MobileVersionSettingPage extends Page
{
    protected static string|\BackedEnum|null $navigationIcon  = 'heroicon-o-device-phone-mobile';
    protected static string|\UnitEnum|null   $navigationGroup = 'Manajemen User';
    protected static ?string                 $navigationLabel = 'Force Update Aplikasi';
    protected static ?int                    $navigationSort  = 95;

    public static function canAccess(): bool
    {
        return AdminAccess::can('Manajemen User');
    }

    protected string $view = 'filament.pages.mobile-version-setting';

    public ?array $data = [];

    public function mount(): void
    {
        $this->data = [
            'force_mobile_update'   => (bool) AppSetting::get('force_mobile_update', config('sims.force_mobile_update', true)),
            'min_mobile_version'    => (string) AppSetting::get('min_mobile_version', config('sims.min_mobile_version', '1.5.8')),
            'latest_mobile_version' => (string) AppSetting::get('latest_mobile_version', config('sims.latest_mobile_version', '1.5.9')),
            'play_store_url'        => (string) AppSetting::get('play_store_url', config('sims.play_store_url', 'https://play.google.com/store/apps/details?id=com.sman1gianyar.sims_mobile')),
            'mobile_update_title'   => (string) AppSetting::get('mobile_update_title', 'Pembaruan Aplikasi Wajib'),
            'mobile_update_message' => (string) AppSetting::get('mobile_update_message', 'Versi baru aplikasi SIMAK DOSMAN telah tersedia. Anda wajib memperbarui aplikasi ke versi terbaru untuk dapat melanjutkan penggunaan aplikasi.'),
        ];
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Kontrol Wajib Update Aplikasi Mobile (Force Update)')
                    ->description('Atur versi minimal aplikasi Android/iOS. Pengguna dengan versi di bawah Versi Minimal Wajib akan dikunci di layar pembaruan dan tidak dapat menggunakan aplikasi sebelum melakukan update.')
                    ->schema([
                        Toggle::make('force_mobile_update')
                            ->label('Aktifkan Paksa Update (Force Update)')
                            ->helperText('Jika aktif, setiap user yang membuka aplikasi dengan versi di bawah Versi Minimal Wajib tidak bisa masuk ke aplikasi sampai melakukan update.')
                            ->default(true),
                        TextInput::make('min_mobile_version')
                            ->label('Versi Minimal Wajib (Contoh: 1.5.8)')
                            ->helperText('Format: X.Y.Z (tanpa huruf v atau nomor build). User dengan versi lebih rendah dari angka ini wajib update.')
                            ->required()
                            ->placeholder('1.5.8'),
                        TextInput::make('latest_mobile_version')
                            ->label('Versi Terbaru Saat Ini (Contoh: 1.5.8)')
                            ->required()
                            ->placeholder('1.5.8'),
                        TextInput::make('play_store_url')
                            ->label('URL Update (Google Play Store / Link Download APK)')
                            ->required()
                            ->url(),
                        TextInput::make('mobile_update_title')
                            ->label('Judul Layar Peringatan Update')
                            ->required(),
                        Textarea::make('mobile_update_message')
                            ->label('Pesan Peringatan Update')
                            ->rows(3)
                            ->required(),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        AppSetting::set('force_mobile_update', (bool) ($this->data['force_mobile_update'] ?? true));
        AppSetting::set('min_mobile_version', trim((string) ($this->data['min_mobile_version'] ?? '1.5.9')));
        AppSetting::set('latest_mobile_version', trim((string) ($this->data['latest_mobile_version'] ?? '1.5.9')));
        AppSetting::set('play_store_url', trim((string) ($this->data['play_store_url'] ?? '')));
        AppSetting::set('mobile_update_title', trim((string) ($this->data['mobile_update_title'] ?? 'Pembaruan Aplikasi Wajib')));
        AppSetting::set('mobile_update_message', trim((string) ($this->data['mobile_update_message'] ?? '')));

        Notification::make()
            ->title('Pengaturan Force Update Disimpan')
            ->body('Versi minimal wajib telah diperbarui ke v' . ($this->data['min_mobile_version'] ?? '1.5.8') . '.')
            ->success()
            ->send();
    }
}

