<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ForgotAttendanceResource\Pages;
use App\Models\Attendance;
use App\Models\ForgotAttendanceRequest;
use App\Services\NotificationService;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use App\Filament\Support\AdminAccess;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;

class ForgotAttendanceResource extends Resource
{
    protected static ?string $model = ForgotAttendanceRequest::class;

    protected static string|\BackedEnum|null $navigationIcon  = 'heroicon-o-clock';
    protected static string|\UnitEnum|null   $navigationGroup = 'Presensi Siswa';
    protected static ?string                 $navigationLabel = 'Lupa Absen';
    protected static ?string                 $modelLabel      = 'Lupa Absen';
    protected static ?string                 $pluralModelLabel = 'Lupa Absen Siswa';
    protected static ?int                    $navigationSort  = 32;

    public static function canAccess(): bool { return AdminAccess::can('Presensi Siswa'); }

    public static function getNavigationBadge(): ?string
    {
        $count = static::getModel()::where('status', 'pending')->count();
        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function canCreate(): bool { return false; }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('student.name')
                    ->label('Siswa')
                    ->searchable()
                    ->sortable()
                    ->weight('semibold')
                    ->description(function (ForgotAttendanceRequest $record) {
                        if (! $record->student) return null;
                        $stats = $record->student->getAttendanceStatsSummary();
                        return "📊 Lupa Absen: {$stats['lupa_absen_total']}x (ACC: {$stats['lupa_absen_approved']})";
                    }),

                TextColumn::make('student.schoolClass.name')
                    ->label('Kelas')
                    ->placeholder('—'),

                TextColumn::make('type')
                    ->label('Jenis')
                    ->badge()
                    ->color(fn (string $state) => match($state) {
                        'pulang'   => 'warning',
                        'keduanya' => 'purple',
                        default    => 'info',
                    })
                    ->formatStateUsing(fn (ForgotAttendanceRequest $record) => $record->typeLabel()),

                TextColumn::make('date')
                    ->label('Tanggal')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('reason')
                    ->label('Alasan')
                    ->limit(20)
                    ->wrap()
                    ->tooltip(fn (ForgotAttendanceRequest $record) => $record->reason),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state) => match($state) {
                        'pending'  => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        default    => 'gray',
                    })
                    ->formatStateUsing(fn (ForgotAttendanceRequest $record) => $record->statusLabel()),

                TextColumn::make('reviewer.name')
                    ->label('Diproses Oleh')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Diajukan')
                    ->since()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('date', 'desc')
            ->filters([
                SelectFilter::make('type')
                    ->label('Jenis Lupa Absen')
                    ->options([
                        'masuk'    => 'Lupa Absen Datang',
                        'pulang'   => 'Lupa Absen Pulang',
                        'keduanya' => 'Lupa Absen Datang & Pulang',
                    ]),

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
                    ->label('Setujui')
                    ->tooltip('Setujui Pengajuan')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->iconButton()
                    ->visible(fn (ForgotAttendanceRequest $record) => $record->isPending())
                    ->form([
                        Textarea::make('teacher_note')
                            ->label('Catatan (opsional)')
                            ->rows(2),
                    ])
                    ->action(function (ForgotAttendanceRequest $record, array $data): void {
                        $type = $record->type ?: 'masuk';
                        $att = Attendance::where('user_id', $record->student_id)
                            ->whereDate('date', $record->date)
                            ->first() ?? new Attendance([
                                'user_id' => $record->student_id,
                                'date'    => $record->date->toDateString(),
                            ]);

                        $att->status          = 'hadir';
                        $att->via_lupa_absen  = true;
                        $att->lupa_absen_type = $type;

                        if ($type === 'masuk') {
                            if (! $att->check_in_time) $att->check_in_time = '07:00:00';
                        } elseif ($type === 'pulang') {
                            if (! $att->check_out_time) $att->check_out_time = '15:30:00';
                        } else {
                            if (! $att->check_in_time) $att->check_in_time = '07:00:00';
                            if (! $att->check_out_time) $att->check_out_time = '15:30:00';
                        }
                        $att->save();

                        $record->update([
                            'status'       => 'approved',
                            'reviewed_by'  => Auth::id(),
                            'reviewed_at'  => now(),
                            'teacher_note' => $data['teacher_note'] ?? null,
                        ]);

                        NotificationService::send(
                            userId: $record->student_id,
                            title:  'Lupa Absen Disetujui',
                            body:   'Pengajuan ' . $record->typeLabel() . ' tanggal ' . $record->date->isoFormat('D MMMM Y') . ' telah disetujui. Presensi dicatat sebagai Hadir.',
                            type:   'success',
                            url:    route('siswa.forgot-attendance.index'),
                        );

                        Notification::make()->title('Disetujui — presensi dicatat Hadir (' . $record->typeLabel() . ')')->success()->send();
                    }),

                Action::make('reject')
                    ->label('Tolak')
                    ->tooltip('Tolak Pengajuan')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->iconButton()
                    ->visible(fn (ForgotAttendanceRequest $record) => $record->isPending())
                    ->form([
                        Textarea::make('teacher_note')
                            ->label('Alasan Penolakan')
                            ->required()
                            ->rows(3),
                    ])
                    ->action(function (ForgotAttendanceRequest $record, array $data): void {
                        $record->update([
                            'status'       => 'rejected',
                            'reviewed_by'  => Auth::id(),
                            'reviewed_at'  => now(),
                            'teacher_note' => $data['teacher_note'],
                        ]);

                        NotificationService::send(
                            userId: $record->student_id,
                            title:  'Lupa Absen Ditolak',
                            body:   'Pengajuan lupa absen tanggal ' . $record->date->isoFormat('D MMMM Y') . ' ditolak. Alasan: ' . $data['teacher_note'],
                            type:   'warning',
                            url:    route('siswa.forgot-attendance.index'),
                        );

                        Notification::make()->title('Pengajuan ditolak')->danger()->send();
                    }),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    BulkAction::make('bulk_approve')
                        ->label('Setujui Terpilih')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->modalHeading('Setujui Pengajuan Lupa Absen Terpilih?')
                        ->action(function (Collection $records): void {
                            $approvedCount = 0;
                            foreach ($records as $record) {
                                if ($record->isPending()) {
                                    $type = $record->type ?: 'masuk';
                                    $att = Attendance::where('user_id', $record->student_id)
                                        ->whereDate('date', $record->date)
                                        ->first() ?? new Attendance([
                                            'user_id' => $record->student_id,
                                            'date'    => $record->date->toDateString(),
                                        ]);

                                    $att->status          = 'hadir';
                                    $att->via_lupa_absen  = true;
                                    $att->lupa_absen_type = $type;

                                    if ($type === 'masuk') {
                                        if (! $att->check_in_time) $att->check_in_time = '07:00:00';
                                    } elseif ($type === 'pulang') {
                                        if (! $att->check_out_time) $att->check_out_time = '15:30:00';
                                    } else {
                                        if (! $att->check_in_time) $att->check_in_time = '07:00:00';
                                        if (! $att->check_out_time) $att->check_out_time = '15:30:00';
                                    }
                                    $att->save();

                                    $record->update([
                                        'status'      => 'approved',
                                        'reviewed_by' => Auth::id(),
                                        'reviewed_at' => now(),
                                    ]);

                                    NotificationService::send(
                                        userId: $record->student_id,
                                        title:  'Lupa Absen Disetujui',
                                        body:   'Pengajuan ' . $record->typeLabel() . ' tanggal ' . $record->date->isoFormat('D MMMM Y') . ' telah disetujui. Presensi dicatat sebagai Hadir.',
                                        type:   'success',
                                        url:    route('siswa.forgot-attendance.index'),
                                    );

                                    $approvedCount++;
                                }
                            }

                            Notification::make()
                                ->title("{$approvedCount} pengajuan lupa absen disetujui (presensi dicatat Hadir).")
                                ->success()
                                ->send();
                        }),

                    BulkAction::make('bulk_reject')
                        ->label('Tolak Terpilih')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->form([
                            Textarea::make('teacher_note')
                                ->label('Alasan Penolakan (untuk semua yang terpilih)')
                                ->default('Ditolak oleh admin via aksi massal.')
                                ->required()
                                ->rows(2),
                        ])
                        ->action(function (Collection $records, array $data): void {
                            $rejectedCount = 0;
                            foreach ($records as $record) {
                                if ($record->isPending()) {
                                    $record->update([
                                        'status'       => 'rejected',
                                        'reviewed_by'  => Auth::id(),
                                        'reviewed_at'  => now(),
                                        'teacher_note' => $data['teacher_note'],
                                    ]);

                                    NotificationService::send(
                                        userId: $record->student_id,
                                        title:  'Lupa Absen Ditolak',
                                        body:   'Pengajuan lupa absen tanggal ' . $record->date->isoFormat('D MMMM Y') . ' ditolak. Alasan: ' . $data['teacher_note'],
                                        type:   'warning',
                                        url:    route('siswa.forgot-attendance.index'),
                                    );

                                    $rejectedCount++;
                                }
                            }

                            Notification::make()
                                ->title("{$rejectedCount} pengajuan ditolak.")
                                ->danger()
                                ->send();
                        }),

                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListForgotAttendances::route('/'),
        ];
    }
}
