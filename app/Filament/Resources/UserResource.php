<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\SchoolClass;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\HtmlString;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|\BackedEnum|null $navigationIcon  = 'heroicon-o-academic-cap';
    protected static string|\UnitEnum|null   $navigationGroup = 'Manajemen User';
    protected static ?string $navigationLabel = 'Data Siswa';
    protected static ?string $modelLabel       = 'Siswa';
    protected static ?string $pluralModelLabel = 'Data Siswa';
    protected static ?int $navigationSort = 1;

    public static function canAccess(): bool { return auth()->user()?->role === 'admin'; }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Identitas')->schema([
                TextInput::make('name')
                    ->label('Nama Lengkap')
                    ->required()
                    ->maxLength(100),

                TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->required()
                    ->unique(ignoreRecord: true),

                Select::make('role')
                    ->label('Role')
                    ->options([
                        'siswa'           => 'Siswa',
                        'pengelola' => 'Siswa Pengelola',
                    ])
                    ->default('siswa')
                    ->required()
                    ->live(),

                TextInput::make('phone')
                    ->label('No. HP')
                    ->tel()
                    ->maxLength(20),

                Placeholder::make('password_info')
                    ->label('Info Password')
                    ->content('🔑 Password pertama otomatis diset sama dengan NISN (Siswa), NIP (Guru), atau No. HP (Orang Tua)')
                    ->helperText('Pengguna dapat memperbarui password setelah login pertama kali.'),
            ])->columns(2),

            Section::make('Data Siswa')
                ->schema([
                    TextInput::make('nisn')
                        ->label('NISN')
                        ->maxLength(10)
                        ->minLength(10)
                        ->regex('/^\d{10}$/')
                        ->unique(ignoreRecord: true)
                        ->placeholder('0001234567')
                        ->helperText('10 digit angka, termasuk angka 0 di depan (contoh: 0002349876)'),

                    TextInput::make('nis')
                        ->label('NIS')
                        ->maxLength(20)
                        ->unique(ignoreRecord: true)
                        ->helperText('Nomor Induk Siswa lokal sekolah'),

                    Select::make('class_id')
                        ->label('Kelas')
                        ->options(SchoolClass::orderBy('grade')->orderBy('name')->pluck('name', 'id'))
                        ->searchable()
                        ->preload(),

                    Select::make('gender')
                        ->label('Jenis Kelamin')
                        ->options(['L' => 'Laki-laki', 'P' => 'Perempuan'])
                        ->nullable(),

                    TextInput::make('parent_name')
                        ->label('Nama Orang Tua / Wali')
                        ->maxLength(100),

                    TextInput::make('parent_phone')
                        ->label('HP Orang Tua')
                        ->tel()
                        ->maxLength(20),

                    DatePicker::make('birth_date')
                        ->label('Tanggal Lahir')
                        ->displayFormat('d/m/Y'),

                    TextInput::make('address')
                        ->label('Alamat')
                        ->maxLength(255),
                ])
                ->columns(2)
                ->visible(fn () => true),
        ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Identitas Siswa')->schema([
                ImageEntry::make('photo')
                    ->label('Foto Profil')
                    ->disk('public')
                    ->imageWidth(120)
                    ->imageHeight(120)
                    ->square()
                    ->defaultImageUrl(fn (User $record): string => 'https://ui-avatars.com/api/?name=' . urlencode($record->name) . '&size=120&background=4f46e5&color=fff&bold=true')
                    ->extraImgAttributes(['style' => 'border-radius:0.5rem; object-fit:cover;'])
                    ->columnSpan(1),

                Grid::make(2)->schema([
                    TextEntry::make('name')
                        ->label('Nama Lengkap')
                        ->weight('bold'),

                    TextEntry::make('email')
                        ->label('Email'),

                    TextEntry::make('role')
                        ->label('Role')
                        ->badge()
                        ->color(fn (string $state) => match ($state) {
                            'siswa'           => 'success',
                            'pengelola' => 'primary',
                            default           => 'gray',
                        })
                        ->formatStateUsing(fn ($state) => match ($state) {
                            'siswa'           => 'Siswa',
                            'pengelola' => 'Siswa Pengelola',
                            default           => $state,
                        }),

                    TextEntry::make('phone')
                        ->label('No. HP')
                        ->placeholder('—'),
                ])->columnSpan(3),
            ])->columns(4),

            Section::make('Data Kesiswaan')->schema([
                TextEntry::make('nisn')
                    ->label('NISN')
                    ->placeholder('—')
                    ->fontFamily('mono')
                    ->color(fn (?string $state) => (filled($state) && strlen(trim($state)) < 10) ? 'danger' : null)
                    ->helperText(fn (?string $state) => (filled($state) && strlen(trim($state)) < 10) ? '⚠️ NISN kurang dari 10 digit (' . strlen(trim($state)) . ' digit)' : null)
                    ->copyable(),

                TextEntry::make('nis')
                    ->label('NIS')
                    ->placeholder('—')
                    ->fontFamily('mono')
                    ->copyable(),

                TextEntry::make('schoolClass.name')
                    ->label('Kelas')
                    ->placeholder('—'),

                TextEntry::make('gender')
                    ->label('Jenis Kelamin')
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'L' => 'Laki-laki',
                        'P' => 'Perempuan',
                        default => '—',
                    })
                    ->placeholder('—'),

                TextEntry::make('birth_date')
                    ->label('Tanggal Lahir')
                    ->date('d/m/Y')
                    ->placeholder('—'),

                TextEntry::make('address')
                    ->label('Alamat')
                    ->placeholder('—')
                    ->columnSpanFull(),
            ])->columns(2),

            Section::make('Data Orang Tua / Wali')->schema([
                TextEntry::make('parent_name')
                    ->label('Nama Orang Tua / Wali')
                    ->placeholder('—'),

                TextEntry::make('parent_phone')
                    ->label('No. HP Orang Tua')
                    ->placeholder('—'),
            ])->columns(2),

            Section::make('Perangkat Mobile (Anti-Titip Absen)')
                ->icon('heroicon-o-device-phone-mobile')
                ->description('Maks. ' . \App\Models\User::MAX_DEVICES . ' perangkat per akun')
                ->schema([
                    IconEntry::make('device_bound')
                        ->label('Status Perangkat')
                        ->getStateUsing(fn (User $record): bool => $record->hasDeviceLocked())
                        ->boolean()
                        ->trueIcon('heroicon-o-lock-closed')
                        ->falseIcon('heroicon-o-lock-open')
                        ->trueColor('success')
                        ->falseColor('warning'),

                    TextEntry::make('device_count')
                        ->label('Jumlah Perangkat')
                        ->getStateUsing(fn (User $record): string =>
                            $record->deviceCount() . ' / ' . \App\Models\User::MAX_DEVICES
                        )
                        ->badge()
                        ->color(fn (User $record): string =>
                            $record->deviceCount() >= \App\Models\User::MAX_DEVICES ? 'danger' : 'success'
                        ),

                    TextEntry::make('devices_list')
                        ->label('Device ID Terdaftar')
                        ->getStateUsing(fn (User $record): string =>
                            $record->devices()->orderByDesc('last_login_at')->get()
                                ->map(fn ($d) => '••••' . substr($d->device_id, -8)
                                    . '  (' . ($d->last_login_at?->diffForHumans() ?? '—') . ')')
                                ->join("\n") ?: '—'
                        )
                        ->fontFamily('mono')
                        ->placeholder('Belum ada perangkat terdaftar'),
                ])
                ->columns(3),
        ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereIn('role', ['siswa', 'pengelola']);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('no_urut')
                    ->label('No.')
                    ->rowIndex()
                    ->alignCenter()
                    ->width('48px'),

                ViewColumn::make('photo')
                    ->label('Foto')
                    ->view('filament.tables.columns.photo-lightbox')
                    ->alignCenter()
                    ->width('64px'),

                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable(['name', 'email'])
                    ->sortable()
                    ->wrap()
                    ->lineClamp(2)
                    ->width('260px')
                    ->description(fn (User $record): ?HtmlString => $record->email
                        ? new HtmlString(Blade::render(
                            '<x-filament::badge color="info" size="xs">{{ $email }}</x-filament::badge>',
                            ['email' => $record->email],
                        ))
                        : null
                    ),

                TextColumn::make('phone')
                    ->label('No. HP Siswa')
                    ->placeholder('—')
                    ->searchable()
                    ->toggleable()
                    ->wrap()
                    ->width('130px'),

                TextColumn::make('parent_phone')
                    ->label('No. HP Ortu')
                    ->placeholder('—')
                    ->searchable()
                    ->toggleable()
                    ->wrap()
                    ->width('130px'),

                TextColumn::make('role')
                    ->label('Role')
                    ->badge()
                    ->width('110px')
                    ->color(fn (string $state) => match ($state) {
                        'admin'           => 'danger',
                        'guru'            => 'warning',
                        'siswa'           => 'success',
                        'pengelola' => 'primary',
                        default           => 'gray',
                    })
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'admin'           => 'Admin',
                        'guru'            => 'Guru',
                        'siswa'           => 'Siswa',
                        'pengelola' => 'Siswa Pengelola',
                        default           => $state,
                    }),

                TextColumn::make('schoolClass.name')
                    ->label('Kelas')
                    ->placeholder('—')
                    ->wrap()
                    ->width('90px'),

                TextColumn::make('nisn_nip')
                    ->label('NISN / NIP')
                    ->getStateUsing(fn (User $record): ?string => match ($record->role) {
                        'guru'                       => $record->nip,
                        'siswa', 'pengelola'   => $record->nisn ?? $record->nis,
                        default                      => null,
                    })
                    ->placeholder('—')
                    ->searchable(query: fn ($query, string $search) => $query
                        ->where('nisn', 'like', "%{$search}%")
                        ->orWhere('nis',  'like', "%{$search}%")
                        ->orWhere('nip',  'like', "%{$search}%"))
                    ->copyable()
                    ->fontFamily('mono')
                    ->color(function (User $record): ?string {
                        if (in_array($record->role, ['siswa', 'pengelola'], true)) {
                            $val = trim((string) ($record->nisn ?? $record->nis));
                            if (strlen($val) > 0 && strlen($val) < 10) {
                                return 'danger';
                            }
                        }
                        return null;
                    })
                    ->extraAttributes(function (User $record): array {
                        if (in_array($record->role, ['siswa', 'pengelola'], true)) {
                            $val = trim((string) ($record->nisn ?? $record->nis));
                            if (strlen($val) > 0 && strlen($val) < 10) {
                                return [
                                    'class' => 'font-bold text-danger-600 dark:text-danger-400',
                                    'title' => '⚠️ NISN kurang dari 10 digit (' . strlen($val) . ' digit)',
                                ];
                            }
                        }
                        return [];
                    })
                    ->width('130px')
                    ->description(function (User $record): ?string {
                        if ($record->role === 'guru') return 'NIP';
                        $val = trim((string) ($record->nisn ?? $record->nis));
                        $type = $record->nisn ? 'NISN' : 'NIS';
                        if (in_array($record->role, ['siswa', 'pengelola'], true) && strlen($val) > 0 && strlen($val) < 10) {
                            return "{$type} (⚠️ " . strlen($val) . " digit)";
                        }
                        return $type;
                    }),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->width('100px')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('nisn_length')
                    ->label('Validasi Digit NISN')
                    ->options([
                        'invalid_9' => '⚠️ NISN 9 Digit (Kurang 0 di Depan)',
                        'less_10'   => '⚠️ Semua NISN < 10 Digit',
                        'valid_10'  => '✅ NISN Valid (10 Digit)',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return match ($data['value'] ?? null) {
                            'invalid_9' => $query->whereIn('role', ['siswa', 'pengelola'])
                                                 ->whereRaw('LENGTH(TRIM(nisn)) = 9'),
                            'less_10'   => $query->whereIn('role', ['siswa', 'pengelola'])
                                                 ->whereRaw('LENGTH(TRIM(nisn)) > 0 AND LENGTH(TRIM(nisn)) < 10'),
                            'valid_10'  => $query->whereIn('role', ['siswa', 'pengelola'])
                                                 ->whereRaw('LENGTH(TRIM(nisn)) >= 10'),
                            default     => $query,
                        };
                    }),

                SelectFilter::make('role')
                    ->label('Tipe Siswa')
                    ->options([
                        'siswa'           => 'Siswa',
                        'pengelola' => 'Siswa Pengelola',
                    ]),

                SelectFilter::make('class_id')
                    ->label('Filter Kelas')
                    ->relationship('schoolClass', 'name'),

                TernaryFilter::make('device_bound')
                    ->label('Status Perangkat')
                    ->nullable()
                    ->trueLabel('Terdaftar')
                    ->falseLabel('Belum Terdaftar')
                    ->queries(
                        true:  fn (Builder $q) => $q->whereHas('devices'),
                        false: fn (Builder $q) => $q->whereDoesntHave('devices'),
                        blank: fn (Builder $q) => $q,
                    ),
            ])
            ->recordUrl(fn (User $record): ?string => in_array($record->role, ['siswa', 'pengelola'])
                ? static::getUrl('view', ['record' => $record])
                : null
            )
            ->recordActions([
                ViewAction::make()->iconButton()
                    ->visible(fn (User $record): bool => in_array($record->role, ['siswa', 'pengelola'])),
                EditAction::make()->iconButton(),
                Action::make('padSingleNisn')
                    ->label('Fix NISN (+0)')
                    ->icon('heroicon-o-plus-circle')
                    ->color('warning')
                    ->iconButton()
                    ->visible(fn (User $record): bool => in_array($record->role, ['siswa', 'pengelola']) && strlen(trim((string) $record->nisn)) === 9)
                    ->tooltip('Tambah 0 di depan NISN (menjadi 10 digit)')
                    ->requiresConfirmation()
                    ->modalHeading('Tambahkan angka 0 di depan NISN?')
                    ->modalDescription(fn (User $record): string => "Ubah NISN '{$record->nisn}' milik {$record->name} menjadi '0{$record->nisn}' (10 digit)?")
                    ->action(function (User $record): void {
                        $oldNisn = trim((string) $record->nisn);
                        $newNisn = '0' . $oldNisn;
                        $record->update(['nisn' => $newNisn]);
                        Notification::make()
                            ->title("NISN {$record->name} berhasil diubah ke 10 digit: {$newNisn}.")
                            ->success()
                            ->send();
                    }),
                Action::make('resetDevice')
                    ->label('Reset Perangkat')
                    ->icon(fn (User $record): string => $record->hasDeviceLocked()
                        ? 'heroicon-o-lock-closed'
                        : 'heroicon-o-lock-open'
                    )
                    ->color(fn (User $record): string => $record->hasDeviceLocked() ? 'success' : 'gray')
                    ->iconButton()
                    ->disabled(fn (User $record): bool => ! $record->hasDeviceLocked())
                    ->tooltip(fn (User $record): string => $record->hasDeviceLocked()
                        ? 'Reset perangkat (' . $record->deviceCount() . '/' . \App\Models\User::MAX_DEVICES . ')'
                        : 'Belum ada perangkat terdaftar'
                    )
                    ->requiresConfirmation()
                    ->modalHeading('Reset Semua Perangkat?')
                    ->modalDescription(fn (User $record): string => sprintf(
                        '%s terdaftar di %d perangkat. Semua akan dihapus dan token dicabut.',
                        $record->name, $record->deviceCount(),
                    ))
                    ->modalSubmitActionLabel('Ya, Reset Semua')
                    ->action(function (User $record): void {
                        $count = $record->deviceCount();
                        $record->resetDevices();
                        Notification::make()
                            ->title("{$record->name}: {$count} perangkat direset.")
                            ->success()
                            ->send();
                    }),
                DeleteAction::make()->iconButton(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    BulkAction::make('bulk_reset_password')
                        ->label('Reset Password Terpilih (Sesuai NISN/NIP/HP)')
                        ->icon('heroicon-o-key')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->modalHeading('Reset Password Akun Terpilih?')
                        ->modalDescription('Password seluruh akun terpilih akan diubah ke default (NISN untuk Siswa, NIP untuk Guru, No. HP untuk Orangtua) dan pengguna WAJIB mengganti password saat login.')
                        ->action(function (Collection $records): void {
                            $resetCount = 0;
                            foreach ($records as $user) {
                                if ($user->role === 'admin' || $user->email === 'playstore.demo@sims.sch.id') {
                                    continue;
                                }

                                $newPasswordRaw = null;
                                if (in_array($user->role, ['siswa', 'pengelola'], true)) {
                                    $newPasswordRaw = trim((string) ($user->nisn ?? $user->nis ?? $user->username));
                                } elseif ($user->role === 'guru') {
                                    $newPasswordRaw = trim((string) ($user->nip ?? $user->username));
                                } elseif ($user->role === 'orangtua') {
                                    $newPasswordRaw = trim((string) $user->phone);
                                }

                                if (filled($newPasswordRaw)) {
                                    $user->update([
                                        'password'             => Hash::make($newPasswordRaw),
                                        'must_change_password' => true,
                                    ]);
                                    $user->resetDevices();
                                    $resetCount++;
                                }
                            }

                            Notification::make()
                                ->title("Password {$resetCount} akun terpilih berhasil direset & ditandai wajib ganti password.")
                                ->success()
                                ->send();
                        }),
                    BulkAction::make('pad_nisn_zeros')
                        ->label('Fix NISN 9 Digit (Tambahkan 0 di Depan)')
                        ->icon('heroicon-o-sparkles')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->modalHeading('Perbaiki NISN 9 Digit Terpilih?')
                        ->modalDescription('Semua siswa terpilih yang memiliki NISN 9 digit akan ditambahkan angka 0 di depan secara otomatis menjadi 10 digit.')
                        ->action(function (Collection $records): void {
                            $fixedCount = 0;
                            foreach ($records as $record) {
                                if (in_array($record->role, ['siswa', 'pengelola'], true) && $record->nisn) {
                                    $val = trim((string) $record->nisn);
                                    if (strlen($val) === 9) {
                                        $record->update(['nisn' => '0' . $val]);
                                        $fixedCount++;
                                    }
                                }
                            }
                            Notification::make()
                                ->title("{$fixedCount} NISN siswa berhasil diperbaiki (ditambahkan 0 di depan).")
                                ->success()
                                ->send();
                        }),
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('name')
            ->defaultPaginationPageOption(100)
            ->paginationPageOptions([10, 25, 50, 100, 200]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit'   => Pages\EditUser::route('/{record}/edit'),
            'view'   => Pages\ViewUser::route('/{record}'),
        ];
    }
}
