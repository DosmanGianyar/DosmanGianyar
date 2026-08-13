<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PasswordResetRequestResource\Pages;
use App\Models\PasswordResetRequest;
use App\Services\NotificationService;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;

class PasswordResetRequestResource extends Resource
{
    protected static ?string $model = PasswordResetRequest::class;

    protected static string|\BackedEnum|null $navigationIcon  = 'heroicon-o-key';
    protected static string|\UnitEnum|null   $navigationGroup = 'Manajemen User';
    protected static ?string $navigationLabel = 'Reset Password';
    protected static ?string $modelLabel       = 'Permintaan Reset Password';
    protected static ?string $pluralModelLabel = 'Permintaan Reset Password';
    protected static ?int    $navigationSort   = 7;

    public static function canAccess(): bool
    {
        return auth()->user()?->role === 'admin';
    }

    public static function getNavigationBadge(): ?string
    {
        $count = static::getModel()::where('status', 'pending')->count();
        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('user.role')
                    ->label('Role')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'guru'  => 'warning',
                        default => 'info',
                    })
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'guru'      => 'Guru',
                        'siswa'     => 'Siswa',
                        'pengelola' => 'Pengelola',
                        default     => $state,
                    }),

                TextColumn::make('user.schoolClass.name')
                    ->label('Kelas')
                    ->placeholder('—')
                    ->badge()
                    ->color('info')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('identifier')
                    ->label('NISN / NIP')
                    ->fontFamily('mono')
                    ->color(function (PasswordResetRequest $record): ?string {
                        if ($record->user?->isSiswa()) {
                            $val = trim((string) $record->identifier);
                            if (strlen($val) > 0 && strlen($val) < 10) {
                                return 'danger';
                            }
                        }
                        return null;
                    })
                    ->copyable()
                    ->searchable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'pending'  => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        default    => 'gray',
                    })
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'pending'  => 'Menunggu',
                        'approved' => 'Disetujui',
                        'rejected' => 'Ditolak',
                        default    => $state,
                    }),

                TextColumn::make('requested_at')
                    ->label('Diajukan')
                    ->since()
                    ->sortable(),

                TextColumn::make('processor.name')
                    ->label('Diproses Oleh')
                    ->placeholder('—'),
            ])
            ->defaultSort('requested_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'pending'  => 'Menunggu',
                        'approved' => 'Disetujui',
                        'rejected' => 'Ditolak',
                    ])
                    ->default('pending'),

                SelectFilter::make('grade')
                    ->label('Filter Per Angkatan')
                    ->options([
                        'X'   => 'Angkatan X',
                        'XI'  => 'Angkatan XI',
                        'XII' => 'Angkatan XII',
                    ])
                    ->query(fn ($query, array $data) => filled($data['value'] ?? null)
                        ? $query->whereHas('user.schoolClass', fn ($q) => $q->where('grade', $data['value']))
                        : $query
                    ),

                SelectFilter::make('class_id')
                    ->label('Filter Kelas')
                    ->options(\App\Models\SchoolClass::orderBy('name')->pluck('name', 'id'))
                    ->query(fn ($query, array $data) => filled($data['value'] ?? null)
                        ? $query->whereHas('user', fn ($q) => $q->where('class_id', $data['value']))
                        : $query
                    ),
            ])
            ->actions([
                Action::make('approve')
                    ->label('Reset Password')
                    ->tooltip('Reset Password (Setujui)')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->iconButton()
                    ->visible(fn (PasswordResetRequest $record) => $record->status === 'pending')
                    ->requiresConfirmation()
                    ->modalHeading('Reset Password')
                    ->modalDescription(fn (PasswordResetRequest $record) => "Reset password {$record->user->name} kembali ke default (NISN/NIP)?")
                    ->action(function (PasswordResetRequest $record): void {
                        $record->approve(Auth::user());

                        $default = $record->user->isSiswa() ? $record->user->nisn : $record->user->nip;
                        NotificationService::send(
                            $record->user_id,
                            'Password Direset',
                            "Password Anda telah direset oleh admin. Silakan login menggunakan {$default} sebagai password, lalu segera ganti password Anda di halaman Profil.",
                            'success',
                        );

                        Notification::make()->title('Password berhasil direset')->success()->send();
                    }),

                Action::make('reject')
                    ->label('Tolak')
                    ->tooltip('Tolak Permintaan')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->iconButton()
                    ->visible(fn (PasswordResetRequest $record) => $record->status === 'pending')
                    ->requiresConfirmation()
                    ->modalHeading('Tolak Permintaan')
                    ->modalDescription(fn (PasswordResetRequest $record) => "Tolak permintaan reset password dari {$record->user->name}?")
                    ->action(function (PasswordResetRequest $record): void {
                        $record->reject(Auth::user());
                        Notification::make()->title('Permintaan ditolak')->success()->send();
                    }),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    BulkAction::make('bulk_approve')
                        ->label('Disetujui & Reset Password Terpilih')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->modalHeading('Setujui Permintaan Reset Password Terpilih?')
                        ->modalDescription('Seluruh pengajuan terpilih akan di-reset password-nya ke default (NISN/NIP) dan pengguna ditandai wajib ganti password.')
                        ->action(function (Collection $records): void {
                            $approvedCount = 0;
                            foreach ($records as $record) {
                                if ($record->status === 'pending') {
                                    $record->approve(Auth::user());
                                    $approvedCount++;
                                }
                            }
                            Notification::make()
                                ->title("{$approvedCount} permintaan reset password berhasil disetujui.")
                                ->success()
                                ->send();
                        }),
                    BulkAction::make('bulk_reject')
                        ->label('Tolak Permintaan Terpilih')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->modalHeading('Tolak Permintaan Reset Password Terpilih?')
                        ->action(function (Collection $records): void {
                            $rejectedCount = 0;
                            foreach ($records as $record) {
                                if ($record->status === 'pending') {
                                    $record->reject(Auth::user());
                                    $rejectedCount++;
                                }
                            }
                            Notification::make()
                                ->title("{$rejectedCount} permintaan reset password ditolak.")
                                ->success()
                                ->send();
                        }),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPasswordResetRequests::route('/'),
        ];
    }
}
