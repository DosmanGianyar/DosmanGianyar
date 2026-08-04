<?php

namespace App\Filament\Pages;

use App\Models\AppSetting;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;

class EvotingSettingPage extends Page
{
    protected static string|\BackedEnum|null $navigationIcon  = 'heroicon-o-power';
    protected static string|\UnitEnum|null   $navigationGroup = 'E-Voting';
    protected static ?string                 $navigationLabel = 'Pengaturan E-Voting';
    protected static ?int                    $navigationSort  = 20;

    public static function canAccess(): bool { return auth()->user()?->role === 'admin'; }

    protected string $view = 'filament.pages.evoting-setting';

    public ?array $data = [];

    public function mount(): void
    {
        $this->data = [
            'is_evoting_active' => AppSetting::isEvotingActive(),
        ];
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Toggle::make('is_evoting_active')
                    ->label('Aktifkan Fitur E-Voting untuk Siswa & Guru')
                    ->helperText('Jika diaktifkan, siswa dan guru dapat melihat dan mengakses tautan E-Voting di web maupun aplikasi mobile. Jika dinonaktifkan, seluruh tautan E-Voting disembunyikan.')
                    ->default(true),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        AppSetting::set('is_evoting_active', (bool) ($data['is_evoting_active'] ?? true));

        Notification::make()
            ->title('Pengaturan E-Voting Berhasil Disimpan')
            ->success()
            ->send();
    }
}
