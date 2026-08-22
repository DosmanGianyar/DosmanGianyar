<?php

namespace App\Filament\Pages;

use App\Filament\Support\AdminAccess;
use App\Models\AttendanceSetting;
use Filament\Actions\Action;
use Filament\Schemas\Components\Actions;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
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
        $this->loadDataFromDatabase();
    }

    public function loadDataFromDatabase(): void
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
                'check_in_open'  => $setting->check_in_open ? substr($setting->check_in_open, 0, 5) : '05:00',
                'check_in_late'  => $setting->check_in_late ? substr($setting->check_in_late, 0, 5) : '07:15',
                'check_in_close' => $setting->check_in_close ? substr($setting->check_in_close, 0, 5) : '08:00',
                'check_out_open' => $setting->check_out_open ? substr($setting->check_out_open, 0, 5) : ($setting->day_of_week === 6 ? '11:00' : '13:30'),
                'check_out_close'=> $setting->check_out_close ? substr($setting->check_out_close, 0, 5) : null,
                'is_active'      => (bool) $setting->is_active,
            ];
        }

        $this->data = ['days' => $daysData];
        if (isset($this->form)) {
            $this->form->fill($this->data);
        }
    }

    public function resetDay(int $dayNum): void
    {
        AttendanceSetting::resetDayToDefault($dayNum);
        $setting = AttendanceSetting::where('day_of_week', $dayNum)->first();

        if ($setting) {
            $this->data['days'][$dayNum] = [
                'day_of_week'    => $setting->day_of_week,
                'day_name'       => $setting->day_name,
                'check_in_open'  => substr($setting->check_in_open, 0, 5),
                'check_in_late'  => substr($setting->check_in_late, 0, 5),
                'check_in_close' => substr($setting->check_in_close, 0, 5),
                'check_out_open' => substr($setting->check_out_open, 0, 5),
                'check_out_close'=> $setting->check_out_close ? substr($setting->check_out_close, 0, 5) : null,
                'is_active'      => (bool) $setting->is_active,
            ];

            if (isset($this->form)) {
                $this->form->fill($this->data);
            }
        }

        Notification::make()
            ->title("Jadwal Hari {$setting->day_name} Direset")
            ->body("Waktu presensi hari {$setting->day_name} telah terisi otomatis dengan jadwal default (Masuk 05:00, Pulang " . substr($setting->check_out_open, 0, 5) . ").")
            ->success()
            ->send();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('resetGlobal')
                ->label('Reset Semua Hari ke Default')
                ->color('danger')
                ->icon('heroicon-o-arrow-path')
                ->requiresConfirmation()
                ->modalHeading('Reset Pengaturan Waktu Presensi Seluruh Hari')
                ->modalDescription('Apakah Anda yakin ingin mengembalikan jam presensi seluruh hari (Senin-Minggu) ke jadwal baku sekolah (Masuk 05:00, Pulang 13:30 / Sabtu 11:00)?')
                ->modalSubmitActionLabel('Ya, Reset Semua Hari')
                ->action(function () {
                    AttendanceSetting::resetToDefault();
                    $this->loadDataFromDatabase();
                    Notification::make()
                        ->title('Pengaturan Seluruh Hari Berhasil Direset')
                        ->body('Seluruh jam presensi telah terisi otomatis dengan jadwal default standar sekolah (Format 24 Jam).')
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

                    Actions::make([
                        Action::make('reset_day_' . $dayNum)
                            ->label('Reset Jam Hari ' . $dayName . ' ke Default')
                            ->icon('heroicon-o-arrow-path')
                            ->color('warning')
                            ->requiresConfirmation()
                            ->modalHeading('Reset Waktu Presensi Hari ' . $dayName)
                            ->modalDescription('Kembalikan jam presensi hari ' . $dayName . ' ke jadwal default baku sekolah?')
                            ->modalSubmitActionLabel('Ya, Reset Hari ' . $dayName)
                            ->action(function () use ($dayNum) {
                                $this->resetDay($dayNum);
                            }),
                    ])->columnSpanFull(),
                ])
                ->columns(2);
        }

        return $schema
            ->components([
                Tabs::make('Jadwal Waktu Presensi Harian')
                    ->tabs($tabs),
            ])
            ->statePath('data');
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
