<?php

namespace App\Filament\Pages;

use App\Models\AttendanceSetting;
use Filament\Forms\Components\TimePicker;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;

class AttendanceSettingPage extends Page
{
    protected static string|\BackedEnum|null $navigationIcon       = 'heroicon-o-clock';
    protected static string|\UnitEnum|null  $navigationGroup      = 'Kesiswaan';
    protected static ?string               $navigationParentItem  = 'Presensi';
    protected static ?string                $navigationLabel      = 'Pengaturan Waktu Presensi';
    protected static ?int                   $navigationSort  = 10;

    protected string $view = 'filament.pages.attendance-setting';

    public ?array $data = [];

    public function mount(): void
    {
        $setting = AttendanceSetting::current();
        $this->data = $setting->only([
            'check_in_open', 'check_in_late', 'check_in_close',
            'check_out_open', 'check_out_close',
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TimePicker::make('check_in_open')
                    ->label('Absen Masuk Dibuka')
                    ->required()
                    ->seconds(false)
                    ->helperText('Paling awal siswa bisa melakukan absen masuk'),

                TimePicker::make('check_in_late')
                    ->label('Batas Hadir Tepat Waktu')
                    ->required()
                    ->seconds(false)
                    ->helperText('Sebelum jam ini = Hadir, sesudahnya = Terlambat'),

                TimePicker::make('check_in_close')
                    ->label('Absen Masuk Ditutup')
                    ->required()
                    ->seconds(false)
                    ->helperText('Sesudah jam ini siswa tidak bisa absen, status = Alpa'),

                TimePicker::make('check_out_open')
                    ->label('Absen Pulang Dibuka')
                    ->required()
                    ->seconds(false)
                    ->helperText('Paling awal siswa bisa melakukan absen pulang'),

                TimePicker::make('check_out_close')
                    ->label('Absen Pulang Ditutup')
                    ->seconds(false)
                    ->nullable()
                    ->helperText('Opsional — biarkan kosong jika tidak ada batas waktu pulang'),
            ])
            ->statePath('data')
            ->columns(2);
    }

    public function save(): void
    {
        $data = $this->form->getState();

        AttendanceSetting::current()->update($data);

        Notification::make()
            ->title('Pengaturan berhasil disimpan')
            ->success()
            ->send();
    }

}
