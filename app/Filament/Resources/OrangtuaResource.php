<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrangtuaResource\Pages;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Hash;

class OrangtuaResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|\BackedEnum|null $navigationIcon  = 'heroicon-o-user-group';
    protected static string|\UnitEnum|null   $navigationGroup = 'Manajemen User';
    protected static ?string $navigationLabel = 'Data Orang Tua';
    protected static ?string $modelLabel       = 'Akun Orang Tua';
    protected static ?string $pluralModelLabel = 'Data Orang Tua';
    protected static ?int    $navigationSort   = 3;

    public static function canAccess(): bool
    {
        return auth()->user()?->role === 'admin'
            || \App\Filament\Support\AdminAccess::can('Kesiswaan & Layanan');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('role', 'orangtua')
            ->with(['children.schoolClass']);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Identitas Akun Orang Tua')->schema([
                TextInput::make('name')
                    ->label('Nama Orang Tua / Wali')
                    ->required()
                    ->maxLength(100)
                    ->placeholder('Contoh: I Made Sudarma'),

                TextInput::make('phone')
                    ->label('Nomor HP (Username Login)')
                    ->tel()
                    ->required()
                    ->maxLength(20)
                    ->unique(
                        table: 'users',
                        column: 'phone',
                        ignoreRecord: true,
                        modifyRuleUsing: fn (\Illuminate\Validation\Rules\Unique $rule) => $rule->where('role', 'orangtua')
                    )
                    ->placeholder('Contoh: 081234567890')
                    ->helperText('📱 Nomor HP ini digunakan sebagai USERNAME dan PASSWORD default untuk login Orang Tua di aplikasi SIMAK DOSMAN (Web & Mobile).'),

                TextInput::make('email')
                    ->label('Email Akun')
                    ->email()
                    ->unique(ignoreRecord: true)
                    ->placeholder('Otomatis: nomorHP@ortu.sims.sch.id')
                    ->helperText('Boleh dikosongkan, sistem akan otomatis menghasilkan email internal.'),

                TextInput::make('password')
                    ->label('Password Baru')
                    ->password()
                    ->revealable()
                    ->placeholder('Kosongkan untuk menyamakan dengan Nomor HP')
                    ->helperText('Jika dikosongkan pada pembuatan baru, otomatis sama dengan nomor HP. Pada edit, kosongkan jika tidak ingin mengubah password.'),

                Select::make('children')
                    ->label('Siswa Terhubung (Anak)')
                    ->multiple()
                    ->searchable()
                    ->preload()
                    ->options(function () {
                        return User::whereIn('role', ['siswa', 'pengelola'])
                            ->with('schoolClass')
                            ->orderBy('name')
                            ->get()
                            ->mapWithKeys(function (User $s) {
                                $className = $s->schoolClass?->name ? ' (' . $s->schoolClass->name . ')' : '';
                                return [$s->id => $s->name . $className];
                            })
                            ->toArray();
                    })
                    ->afterStateHydrated(function (Select $component, ?User $record) {
                        if ($record && $record->exists) {
                            $component->state($record->children()->pluck('users.id')->toArray());
                        }
                    })
                    ->dehydrated(false)
                    ->helperText('Pilih satu atau lebih siswa yang merupakan anak dari orang tua ini.'),
            ])->columns(2),
        ]);
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

                TextColumn::make('name')
                    ->label('Nama Orang Tua')
                    ->searchable()
                    ->sortable()
                    ->wrap()
                    ->width('220px'),

                TextColumn::make('phone')
                    ->label('No. HP (Username)')
                    ->searchable()
                    ->copyable()
                    ->fontFamily('mono')
                    ->badge()
                    ->color('info')
                    ->width('160px')
                    ->tooltip('Klik untuk menyalin No. HP / Username'),

                TextColumn::make('children_names')
                    ->label('Siswa Terhubung (Anak)')
                    ->getStateUsing(function (User $record): string {
                        return $record->children
                            ->map(fn ($c) => $c->name . ($c->schoolClass ? ' (' . $c->schoolClass->name . ')' : ''))
                            ->join(', ') ?: '—';
                    })
                    ->badge()
                    ->color(fn (string $state) => $state === '—' ? 'gray' : 'success')
                    ->wrap(),

                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('devices_count')
                    ->counts('devices')
                    ->label('Perangkat')
                    ->badge()
                    ->color(fn ($state) => $state > 0 ? 'success' : 'gray')
                    ->alignCenter()
                    ->width('100px'),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('has_children')
                    ->label('Koneksi Siswa')
                    ->placeholder('Semua')
                    ->trueLabel('Sudah Terhubung ke Siswa')
                    ->falseLabel('Belum Terhubung ke Siswa')
                    ->queries(
                        true:  fn (Builder $q) => $q->has('children'),
                        false: fn (Builder $q) => $q->doesntHave('children'),
                        blank: fn (Builder $q) => $q,
                    ),
            ])
            ->recordActions([
                Action::make('resetPassword')
                    ->label('Reset Password')
                    ->icon('heroicon-o-key')
                    ->color('warning')
                    ->iconButton()
                    ->tooltip('Reset Password Akun Orang Tua')
                    ->modalHeading(fn (User $record): string => "Reset Password: {$record->name}")
                    ->modalDescription(fn (User $record): string => "Atur ulang password untuk akun orang tua '{$record->name}'. Nilai default terisi nomor HP pengguna.")
                    ->modalSubmitActionLabel('Simpan Password')
                    ->form([
                        TextInput::make('new_password')
                            ->label('Password Baru')
                            ->default(fn (User $record): string => (string) $record->phone)
                            ->required()
                            ->minLength(6)
                            ->helperText('Default: sama dengan nomor HP. Pengguna dapat langsung login dengan password ini.'),
                    ])
                    ->action(function (User $record, array $data): void {
                        $newPass = trim($data['new_password']);
                        $record->update([
                            'password'             => Hash::make($newPass),
                            'must_change_password' => false,
                        ]);
                        $record->resetDevices();

                        Notification::make()
                            ->title("Password {$record->name} berhasil diubah.")
                            ->body("Password baru: {$newPass}")
                            ->success()
                            ->send();
                    }),
                EditAction::make()->iconButton(),
                DeleteAction::make()->iconButton(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('bulk_reset_to_phone')
                        ->label('Reset Password Terpilih (ke No. HP)')
                        ->icon('heroicon-o-key')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->modalHeading('Reset Password Akun Terpilih ke No. HP?')
                        ->modalDescription('Seluruh akun orang tua yang dipilih akan di-reset password-nya menjadi nomor HP masing-masing.')
                        ->modalSubmitActionLabel('Ya, Reset Semua')
                        ->action(function (Collection $records): void {
                            $count = 0;
                            foreach ($records as $user) {
                                if ($user->role !== 'orangtua' || empty($user->phone)) {
                                    continue;
                                }
                                $user->update([
                                    'password'             => Hash::make($user->phone),
                                    'must_change_password' => false,
                                ]);
                                $user->resetDevices();
                                $count++;
                            }

                            Notification::make()
                                ->title("Password {$count} akun orang tua berhasil di-reset ke Nomor HP masing-masing.")
                                ->success()
                                ->send();
                        }),
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('name')
            ->defaultPaginationPageOption(50)
            ->paginationPageOptions([10, 25, 50, 100]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListOrangtuas::route('/'),
            'create' => Pages\CreateOrangtua::route('/create'),
            'edit'   => Pages\EditOrangtua::route('/{record}/edit'),
        ];
    }
}
