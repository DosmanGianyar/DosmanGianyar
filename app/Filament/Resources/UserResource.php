<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\SchoolClass;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\DatePicker;
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

                TextInput::make('password')
                    ->label('Password')
                    ->password()
                    ->revealable()
                    ->default('Dosman123')
                    ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                    ->dehydrated(fn ($state) => filled($state))
                    ->required(fn (string $operation) => $operation === 'create')
                    ->helperText('Default: Dosman123 — kosongkan jika tidak ingin mengubah password'),
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
                    ->width('130px')
                    ->description(fn (User $record): ?string => match ($record->role) {
                        'guru'                     => 'NIP',
                        'siswa', 'pengelola' => $record->nisn ? 'NISN' : 'NIS',
                        default                    => null,
                    }),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->width('100px')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
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
                ActionGroup::make([
                    ViewAction::make()->iconButton()
                        ->visible(fn (User $record): bool => in_array($record->role, ['siswa', 'pengelola'])),
                    EditAction::make()->iconButton(),
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
                    ->dropdown(false)
                    ->extraAttributes([
                        'style' => 'display:grid;grid-template-columns:repeat(2,1fr);gap:2px;width:fit-content;',
                    ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('name');
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
