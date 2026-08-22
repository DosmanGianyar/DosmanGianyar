<?php

namespace App\Filament\Pages;

use App\Filament\Support\AdminAccess;
use App\Models\AttendanceSetting;
use Filament\Actions\Action;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;

class AttendanceSettingPage extends Page
{
    protected static string|\BackedEnum|null $navigationIcon  = 'heroicon-o-clock';
    protected static string|\UnitEnum|null   $navigationGroup = 'Presensi Siswa';
    protected static ?string                 $navigationLabel = 'Pengaturan Waktu Presensi';
    protected static ?int                    $navigationSort  = 10;

    public static function canAccess(): bool { return AdminAccess::can('Presensi Siswa'); }

    protected string $view = 'filament.pages.attendance-setting';

    public ?array $data = [];

    public function mount(): void
    {
        $settings = AttendanceSetting::orderBy('day_of_week')->get();
        if ($settings->count() < 7) {
            AttendanceSetting::resetToDefault();
            $settings = AttendanceSetting::orderBy('day_of_week')->get();
        }

        $daysData = [];
        foreach ($settings as $setting) {
            $daysData[$setting->day_of_week] = [
                'day_of_week'    => $setting->day_of_week,
                'day_name'       => $setting->day_name,
                'check_in_open'  => $setting->check_in_open,
                'check_in_late'  => $setting->check_in_late,
                'check_in_close' => $setting->check_in_close,
                'check_out_open' => $setting->check_out_open,
                'check_out_close'=> $setting->check_out_close,
                'is_active'      => (bool) $setting->is_active,
            ];
        }

        $this->data = ['days' => $daysData];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('resetDefault')
                ->label('Reset ke Jadwal Default')
                ->color('danger')
                ->icon('heroicon-o-arrow-path')
                ->requiresConfirmation()
                ->modalHeading('Reset Pengaturan Waktu Presensi')
                ->modalDescription('Apakah Anda yakin ingin mengembalikan jam presensi seluruh hari (Senin-Sabtu) ke jadwal baku sekolah (Masuk 05:00, Pulang 13:30 / Sabtu 11:00)?')
                ->modalSubmitActionLabel('Ya, Reset Sekarang')
                ->action(function () {
                    AttendanceSetting::resetToDefault();
                    $this->mount();
                    Notification::make()
                        ->title('Pengaturan Berhasil Direset')
                        ->body('Seluruh jam presensi telah dikembalikan ke jadwal default standar sekolah (Format 24 Jam).')
                        ->success()
                        ->send();
                }),
        ];
    }

    public function form(Schema $schema): Schema
    {
        $dayConfigs = [
            1 => 'Senin',
            2 => 'Selasa',
            3 => 'Rabu',
            4 => 'Kamis',
            5 => 'Jumat',
            6 => 'Sabtu',
            7 => 'Minggu',
        ];

        $tabs = [];
        foreach ($dayConfigs as $dayNum => $dayName) {
            $subtitle = match ($dayNum) {
                6 => ' (Pulang Awal / Ekstrakurikuler)',
                7 => ' (Hari Libur)',
                default => ' (KBM Penuh)',
            };

            $tabs[] = Tab::make($dayName . $subtitle)
                ->schema([
                    Toggle::make("days.{$dayNum}.is_active")
                        ->label("Status Presensi Hari {$dayName}")
                        ->helperText("Aktifkan untuk membuka sistem presensi pada hari {$dayName}")
                        ->columnSpanFull(),

                    TimePicker::make("days.{$dayNum}.check_in_open")
                        ->label('Absen Masuk Dibuka')
                        ->required()
                        ->seconds(false)
                        ->helperText('Paling awal siswa bisa absen masuk (Format 24 Jam, Contoh: 05:00)'),

                    TimePicker::make("days.{$dayNum}.check_in_late")
                        ->label('Batas Hadir Tepat Waktu')
                        ->required()
                        ->seconds(false)
                        ->helperText('Sebelum jam ini = Hadir, sesudahnya = Terlambat (Contoh: 07:15)'),

                    TimePicker::make("days.{$dayNum}.check_in_close")
                        ->label('Absen Masuk Ditutup')
                        ->required()
                        ->seconds(false)
                        ->helperText('Sesudah jam ini siswa tidak bisa absen, status = Alpa (Contoh: 08:00)'),

                    TimePicker::make("days.{$dayNum}.check_out_open")
                        ->label('Absen Pulang Dibuka')
                        ->required()
                        ->seconds(false)
                        ->helperText('Paling awal siswa bisa melakukan absen pulang hari ini (Contoh: 13:30 atau 11:00)'),

                    TimePicker::make("days.{$dayNum}.check_out_close")
                        ->label('Absen Pulang Ditutup')
                        ->seconds(false)
                        ->nullable()
                        ->helperText('Opsional — Kosongkan jika tidak ada batas waktu jam pulang'),
                ])
                ->columns(2);
        }

        return $schema
            ->components([
                Tabs::make('Jadwal Waktu Presensi Harian')
                    ->tabs($tabs),
            ]);
    }

    public function save(): void
    {
        $days = $this->data['days'] ?? [];

        foreach ($days as $dayNum => $dayData) {
            AttendanceSetting::where('day_of_week', $dayNum)->update([
                'check_in_open'   => $dayData['check_in_open'],
                'check_in_late'   => $dayData['check_in_late'],
                'check_in_close'  => $dayData['check_in_close'],
                'check_out_open'  => $dayData['check_out_open'],
                'check_out_close' => $dayData['check_out_close'] ?? null,
                'is_active'       => (bool) ($dayData['is_active'] ?? true),
            ]);
        }

        Notification::make()
            ->title('Pengaturan Berhasil Disimpan')
            ->body('Jadwal waktu presensi harian telah diperbarui.')
            ->success()
            ->send();
    }
}
