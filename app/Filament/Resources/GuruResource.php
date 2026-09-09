<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GuruResource\Pages;
use App\Models\Subject;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Hash;

class GuruResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|\BackedEnum|null $navigationIcon  = 'heroicon-o-briefcase';
    protected static string|\UnitEnum|null   $navigationGroup = 'Manajemen User';
    protected static ?string $navigationLabel = 'Data Guru & Pegawai';
    protected static ?string $modelLabel       = 'Guru / Pegawai / Admin';
    protected static ?string $pluralModelLabel = 'Data Guru & Pegawai';
    protected static ?int    $navigationSort   = 2;

    // ── Scope: guru, pegawai, dan admin ───────────────────────────────────────

    public static function canAccess(): bool { return auth()->user()?->role === 'admin'; }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereIn('role', ['guru', 'pegawai', 'admin']);
    }

    // ── Form ──────────────────────────────────────────────────────────────────

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
                        'guru'    => 'Guru',
                        'pegawai' => 'Pegawai',
                        'admin'   => 'Admin',
                    ])
                    ->default('guru')
                    ->required(),

                TextInput::make('phone')
                    ->label('No. HP')
                    ->tel()
                    ->maxLength(20),

                Placeholder::make('password_info')
                    ->label('Info Password')
                    ->content('🔑 Password pertama otomatis diset sama dengan NIP')
                    ->helperText('Guru dapat memperbarui password setelah login pertama kali.'),
            ])->columns(2),

            Section::make('Data Kepegawaian')->schema([
                TextInput::make('nip')
                    ->label('NIP')
                    ->maxLength(30)
                    ->unique(ignoreRecord: true)
                    ->placeholder('198001012006041001')
                    ->dehydrateStateUsing(fn ($state) => $state ? preg_replace('/\s+/', '', $state) : null)
                    ->regex('/^\S+$/')
                    ->validationMessages([
                        'regex' => 'NIP tidak boleh mengandung spasi.',
                    ]),

                Select::make('subjects')
                    ->label('Mata Pelajaran (bisa lebih dari satu)')
                    ->relationship('subjects', 'name')
                    ->multiple()
                    ->preload()
                    ->searchable()
                    ->columnSpanFull(),
            ])->columns(2),
        ]);
    }

    // ── Table ─────────────────────────────────────────────────────────────────

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('no_urut')
                    ->label('No.')
                    ->rowIndex()
                    ->alignCenter()
                    ->width('48px'),

                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->copyable()
                    ->placeholder('—'),

                TextColumn::make('role')
                    ->label('Role')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'admin'   => 'danger',
                        'guru'    => 'warning',
                        'pegawai' => 'info',
                        default   => 'gray',
                    })
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'admin'   => 'Admin',
                        'guru'    => 'Guru',
                        'pegawai' => 'Pegawai',
                        default   => $state,
                    }),

                TextColumn::make('nip')
                    ->label('NIP')
                    ->placeholder('—')
                    ->fontFamily('mono')
                    ->copyable()
                    ->searchable(),

                TextColumn::make('subjects.name')
                    ->label('Mata Pelajaran')
                    ->badge()
                    ->separator(', ')
                    ->placeholder('—'),

                TextColumn::make('phone')
                    ->label('No. HP')
                    ->placeholder('—'),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('role')
                    ->label('Filter Role')
                    ->options([
                        'guru'    => 'Guru',
                        'pegawai' => 'Pegawai',
                        'admin'   => 'Admin',
                    ]),
            ])
            ->recordActions([
                Action::make('resetPassword')
                    ->label('Reset Password')
                    ->icon('heroicon-o-key')
                    ->color('warning')
                    ->iconButton()
                    ->tooltip('Reset Password akun ini ke NIP default atau password baru')
                    ->modalHeading(fn (User $record): string => "Reset Password: {$record->name}")
                    ->modalDescription(fn (User $record): string => "Password akun '{$record->name}' akan diubah. Nilai default terisi NIP pengguna. Anda juga dapat menentukan password baru secara manual tanpa spasi.")
                    ->modalSubmitActionLabel('Ya, Simpan Password')
                    ->form([
                        TextInput::make('new_password')
                            ->label('Password Baru')
                            ->default(fn (User $record): string => $record->nip ?: ($record->email ?: 'Guru123'))
                            ->required()
                            ->minLength(6)
                            ->regex('/^\S+$/')
                            ->validationMessages([
                                'regex' => 'Password tidak boleh mengandung spasi.',
                            ])
                            ->helperText('Default terisi NIP. Pengguna wajib mengganti password saat login pertama kali.'),
                    ])
                    ->action(function (User $record, array $data): void {
                        $newPass = trim($data['new_password']);
                        $record->update([
                            'password'             => Hash::make($newPass),
                            'must_change_password' => true,
                        ]);
                        $record->resetDevices();

                        Notification::make()
                            ->title("Password {$record->name} berhasil di-reset.")
                            ->body("Password baru: {$newPass} (Wajib ganti password saat login).")
                            ->success()
                            ->send();
                    }),
                EditAction::make()->iconButton(),
                DeleteAction::make()->iconButton(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('bulk_reset_password')
                        ->label('Reset Password Terpilih (ke NIP)')
                        ->icon('heroicon-o-key')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->modalHeading('Reset Password Akun Terpilih?')
                        ->modalDescription('Password seluruh guru/pegawai terpilih akan di-reset kembali ke NIP masing-masing dan ditandai wajib mengganti password saat login.')
                        ->modalSubmitActionLabel('Ya, Reset Semua Terpilih')
                        ->action(function (Collection $records): void {
                            $count = 0;
                            foreach ($records as $user) {
                                if ($user->role === 'admin') {
                                    continue;
                                }
                                $newPass = $user->nip ?: ($user->email ?: 'Guru123');
                                $user->update([
                                    'password'             => Hash::make($newPass),
                                    'must_change_password' => true,
                                ]);
                                $user->resetDevices();
                                $count++;
                            }

                            Notification::make()
                                ->title("Password {$count} akun terpilih berhasil di-reset ke NIP default.")
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
            'index'  => Pages\ListGurus::route('/'),
            'create' => Pages\CreateGuru::route('/create'),
            'edit'   => Pages\EditGuru::route('/{record}/edit'),
        ];
    }
}
