<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PasswordResetRequestResource\Pages;
use App\Models\PasswordResetRequest;
use App\Services\NotificationService;
use Filament\Actions\Action;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
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

    public static function canAccess(): bool { return auth()->user()?->role === 'admin'; }

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

                TextColumn::make('identifier')
                    ->label('NISN / NIP')
                    ->fontFamily('mono')
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
            ])
            ->recordActions([
                Action::make('approve')
                    ->label('Reset Password')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
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

                        \Filament\Notifications\Notification::make()->title('Password berhasil direset')->success()->send();
                    }),

                Action::make('reject')
                    ->label('Tolak')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (PasswordResetRequest $record) => $record->status === 'pending')
                    ->requiresConfirmation()
                    ->modalHeading('Tolak Permintaan')
                    ->modalDescription(fn (PasswordResetRequest $record) => "Tolak permintaan reset password dari {$record->user->name}?")
                    ->action(function (PasswordResetRequest $record): void {
                        $record->reject(Auth::user());
                        \Filament\Notifications\Notification::make()->title('Permintaan ditolak')->success()->send();
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPasswordResetRequests::route('/'),
        ];
    }
}
