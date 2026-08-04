<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ExtracurricularMemberResource\Pages;
use App\Models\ExtracurricularMember;
use App\Services\NotificationService;
use Filament\Actions\Action as TableAction;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use App\Filament\Support\AdminAccess;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class ExtracurricularMemberResource extends Resource
{
    protected static ?string $model = ExtracurricularMember::class;

    protected static string|\BackedEnum|null $navigationIcon       = 'heroicon-o-user-plus';
    protected static string|\UnitEnum|null   $navigationGroup      = 'Prestasi & Ekskul';
    protected static ?string                 $navigationLabel      = 'Persetujuan Anggota Ekstra';
    protected static ?string                 $modelLabel           = 'Pendaftaran Ekstrakurikuler';
    protected static ?string                 $pluralModelLabel     = 'Persetujuan Anggota Ekstra';
    protected static ?int                    $navigationSort       = 11;

    public static function canAccess(): bool { return AdminAccess::can('Prestasi & Ekskul'); }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->label('Tanggal Ajuan')
                    ->dateTime('d M Y H:i')
                    ->sortable(),

                TextColumn::make('user.name')
                    ->label('Nama Siswa')
                    ->searchable()
                    ->weight('semibold'),

                TextColumn::make('user.nis')
                    ->label('NIS')
                    ->placeholder('—'),

                TextColumn::make('user.schoolClass.name')
                    ->label('Kelas')
                    ->placeholder('—'),

                TextColumn::make('extracurricular.name')
                    ->label('Ekstrakurikuler')
                    ->searchable()
                    ->weight('bold')
                    ->color('primary'),

                TextColumn::make('status')
                    ->label('Status Pengajuan')
                    ->badge()
                    ->formatStateUsing(fn (ExtracurricularMember $r) => match($r->status) {
                        'pending_join'  => 'Pengajuan Masuk',
                        'pending_leave' => 'Pengajuan Keluar',
                        'active'        => 'Aktif',
                        'rejected'      => 'Ditolak',
                        'inactive'      => 'Tidak Aktif',
                        default         => $r->status,
                    })
                    ->color(fn (string $state) => match($state) {
                        'active'        => 'success',
                        'pending_join'  => 'warning',
                        'pending_leave' => 'danger',
                        'rejected'      => 'gray',
                        default         => 'gray',
                    }),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Filter Status')
                    ->options([
                        'pending_join'  => 'Pengajuan Masuk',
                        'pending_leave' => 'Pengajuan Keluar',
                        'active'        => 'Aktif',
                        'rejected'      => 'Ditolak',
                    ]),

                SelectFilter::make('extracurricular_id')
                    ->label('Ekstrakurikuler')
                    ->relationship('extracurricular', 'name'),
            ])
            ->actions([
                // Setujui
                TableAction::make('approve')
                    ->label('Setujui')
                    ->tooltip('Setujui Pengajuan')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->iconButton()
                    ->visible(fn (ExtracurricularMember $r) => in_array($r->status, ['pending_join', 'pending_leave']))
                    ->action(function (ExtracurricularMember $r) {
                        if ($r->status === 'pending_join') {
                            $r->update([
                                'status'      => 'active',
                                'approved_by' => Auth::id(),
                                'approved_at' => now(),
                            ]);
                            if ($r->user_id) {
                                NotificationService::send(
                                    $r->user_id,
                                    'Pendaftaran Ekstra Disetujui! 🎉',
                                    "Selamat! Pendaftaran Anda di ekstrakurikuler {$r->extracurricular?->name} telah disetujui oleh Sekolah.",
                                    'success'
                                );
                            }
                            Notification::make()->title('Pendaftaran anggota disetujui.')->success()->send();
                        } else {
                            $r->update([
                                'status'      => 'inactive',
                                'approved_by' => Auth::id(),
                                'approved_at' => now(),
                            ]);
                            if ($r->user_id) {
                                NotificationService::send(
                                    $r->user_id,
                                    'Pengajuan Keluar Ekstra Disetujui',
                                    "Pengajuan keluar dari ekstrakurikuler {$r->extracurricular?->name} telah disetujui.",
                                    'info'
                                );
                            }
                            Notification::make()->title('Pengajuan keluar disetujui.')->success()->send();
                        }
                    }),

                // Tolak
                TableAction::make('reject')
                    ->label('Tolak')
                    ->tooltip('Tolak Pengajuan')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->iconButton()
                    ->visible(fn (ExtracurricularMember $r) => in_array($r->status, ['pending_join', 'pending_leave']))
                    ->requiresConfirmation()
                    ->action(function (ExtracurricularMember $r) {
                        if ($r->status === 'pending_join') {
                            $r->update([
                                'status'      => 'rejected',
                                'approved_by' => Auth::id(),
                                'approved_at' => now(),
                            ]);
                            if ($r->user_id) {
                                NotificationService::send(
                                    $r->user_id,
                                    'Pendaftaran Ekstra Ditolak',
                                    "Mohon maaf, pendaftaran Anda di ekstrakurikuler {$r->extracurricular?->name} belum dapat disetujui.",
                                    'danger'
                                );
                            }
                            Notification::make()->title('Pendaftaran anggota ditolak.')->warning()->send();
                        } else {
                            $r->update(['status' => 'active']);
                            Notification::make()->title('Pengajuan keluar ditolak.')->warning()->send();
                        }
                    }),

                // Batalkan / Hapus Kepesertaan (Untuk salah mendaftar)
                TableAction::make('cancel_membership')
                    ->label('Batalkan / Hapus')
                    ->tooltip('Batalkan / Hapus Kepesertaan')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->iconButton()
                    ->requiresConfirmation()
                    ->modalHeading('Batalkan / Hapus Kepesertaan Siswa?')
                    ->modalDescription('Kepesertaan siswa pada ekstrakurikuler ini akan dibatalkan/dihapus sehingga siswa dapat mendaftar ke ekstrakurikuler lain.')
                    ->action(function (ExtracurricularMember $r) {
                        $extraName = $r->extracurricular?->name ?? 'ekstrakurikuler';
                        $userId    = $r->user_id;

                        $r->delete();

                        if ($userId) {
                            NotificationService::send(
                                $userId,
                                'Kepesertaan Ekstra Dibatalkan',
                                "Kepesertaan Anda pada ekstrakurikuler {$extraName} telah dibatalkan oleh Sekolah.",
                                'warning'
                            );
                        }

                        Notification::make()->title('Kepesertaan siswa berhasil dibatalkan.')->success()->send();
                    }),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListExtracurricularMembers::route('/'),
        ];
    }
}
