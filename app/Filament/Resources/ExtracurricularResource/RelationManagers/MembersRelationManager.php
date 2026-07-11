<?php

namespace App\Filament\Resources\ExtracurricularResource\RelationManagers;

use App\Models\ExtracurricularMember;
use Filament\Actions\Action as TableAction;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class MembersRelationManager extends RelationManager
{
    protected static string $relationship = 'members';
    protected static ?string $title = 'Anggota & Permintaan';
    protected static string|\BackedEnum|null $icon = 'heroicon-o-users';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('user.name')
            ->columns([
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

                TextColumn::make('role')
                    ->label('Peran')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => $state === 'ketua' ? 'Ketua' : 'Anggota')
                    ->color(fn (string $state) => $state === 'ketua' ? 'warning' : 'gray'),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (ExtracurricularMember $r) => $r->statusLabel())
                    ->color(fn (string $state) => match($state) {
                        'active'        => 'success',
                        'pending_join'  => 'warning',
                        'pending_leave' => 'danger',
                        default         => 'gray',
                    }),

                TextColumn::make('created_at')
                    ->label('Tanggal Daftar')
                    ->date('d M Y')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'active'        => 'Anggota Aktif',
                        'pending_join'  => 'Menunggu Bergabung',
                        'pending_leave' => 'Mengajukan Keluar',
                    ]),
            ])
            ->headerActions([])
            ->recordActions([
                // Approve pending_join
                TableAction::make('approve_join')
                    ->label('Setujui Bergabung')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (ExtracurricularMember $r) => $r->status === 'pending_join')
                    ->action(function (ExtracurricularMember $r) {
                        $r->update([
                            'status'      => 'active',
                            'approved_by' => Auth::id(),
                            'approved_at' => now(),
                        ]);
                        Notification::make()->title('Anggota disetujui.')->success()->send();
                    }),

                // Reject pending_join
                TableAction::make('reject_join')
                    ->label('Tolak')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (ExtracurricularMember $r) => $r->status === 'pending_join')
                    ->requiresConfirmation()
                    ->action(fn (ExtracurricularMember $r) => $r->delete()),

                // Approve pending_leave
                TableAction::make('approve_leave')
                    ->label('Setujui Keluar')
                    ->icon('heroicon-o-arrow-right-on-rectangle')
                    ->color('danger')
                    ->visible(fn (ExtracurricularMember $r) => $r->status === 'pending_leave')
                    ->requiresConfirmation()
                    ->action(function (ExtracurricularMember $r) {
                        $r->delete();
                        Notification::make()->title('Anggota dikeluarkan.')->success()->send();
                    }),

                // Reject pending_leave (kembalikan ke active)
                TableAction::make('reject_leave')
                    ->label('Tolak Keluar')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('warning')
                    ->visible(fn (ExtracurricularMember $r) => $r->status === 'pending_leave')
                    ->action(function (ExtracurricularMember $r) {
                        $r->update(['status' => 'active']);
                        Notification::make()->title('Permintaan keluar ditolak.')->success()->send();
                    }),

                // Toggle ketua
                TableAction::make('toggle_ketua')
                    ->label(fn (ExtracurricularMember $r) => $r->role === 'ketua' ? 'Cabut Ketua' : 'Jadikan Ketua')
                    ->icon('heroicon-o-star')
                    ->color(fn (ExtracurricularMember $r) => $r->role === 'ketua' ? 'gray' : 'warning')
                    ->visible(fn (ExtracurricularMember $r) => $r->status === 'active')
                    ->action(function (ExtracurricularMember $r) {
                        if ($r->role === 'ketua') {
                            $r->update(['role' => 'member']);
                            Notification::make()->title('Jabatan ketua dicabut.')->success()->send();
                        } else {
                            // Pastikan hanya 1 ketua per ekstra
                            ExtracurricularMember::where('extracurricular_id', $r->extracurricular_id)
                                ->where('role', 'ketua')
                                ->update(['role' => 'member']);
                            $r->update(['role' => 'ketua']);
                            Notification::make()->title("{$r->user->name} ditunjuk sebagai Ketua.")->success()->send();
                        }
                    }),

                // Keluarkan langsung (aktif)
                TableAction::make('remove')
                    ->label('Keluarkan')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->visible(fn (ExtracurricularMember $r) => $r->status === 'active')
                    ->requiresConfirmation()
                    ->action(fn (ExtracurricularMember $r) => $r->delete()),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
