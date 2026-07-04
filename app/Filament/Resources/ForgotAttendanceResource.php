<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ForgotAttendanceResource\Pages;
use App\Models\Attendance;
use App\Models\ForgotAttendanceRequest;
use App\Services\NotificationService;
use Filament\Actions\Action;
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
use Illuminate\Support\Facades\Auth;

class ForgotAttendanceResource extends Resource
{
    protected static ?string $model = ForgotAttendanceRequest::class;

    protected static string|\BackedEnum|null $navigationIcon  = 'heroicon-o-clock';
    protected static string|\UnitEnum|null   $navigationGroup = 'Kesiswaan';
    protected static ?string                 $navigationLabel = 'Lupa Absen';
    protected static ?string                 $modelLabel      = 'Lupa Absen';
    protected static ?string                 $pluralModelLabel = 'Lupa Absen Siswa';
    protected static ?int                    $navigationSort  = 32;

    public static function canAccess(): bool { return AdminAccess::can('Kesiswaan'); }

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
                    ->weight('semibold'),

                TextColumn::make('student.schoolClass.name')
                    ->label('Kelas')
                    ->placeholder('—'),

                TextColumn::make('date')
                    ->label('Tanggal Lupa Absen')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('reason')
                    ->label('Alasan')
                    ->limit(40)
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

                TextColumn::make('created_at')
                    ->label('Diajukan')
                    ->since()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('date', 'desc')
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
                    ->label('Setujui')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (ForgotAttendanceRequest $record) => $record->isPending())
                    ->form([
                        Textarea::make('teacher_note')
                            ->label('Catatan (opsional)')
                            ->rows(2),
                    ])
                    ->action(function (ForgotAttendanceRequest $record, array $data): void {
                        // Catat absensi sebagai hadir
                        Attendance::updateOrCreate(
                            ['user_id' => $record->student_id, 'date' => $record->date->toDateString()],
                            ['status' => 'hadir']
                        );

                        $record->update([
                            'status'       => 'approved',
                            'reviewed_by'  => Auth::id(),
                            'reviewed_at'  => now(),
                            'teacher_note' => $data['teacher_note'] ?? null,
                        ]);

                        NotificationService::send(
                            userId: $record->student_id,
                            title:  'Lupa Absen Disetujui',
                            body:   'Pengajuan lupa absen tanggal ' . $record->date->isoFormat('D MMMM Y') . ' telah disetujui. Presensi dicatat sebagai Hadir.',
                            type:   'success',
                            url:    route('siswa.forgot-attendance.index'),
                        );

                        Notification::make()->title('Disetujui — presensi dicatat Hadir')->success()->send();
                    }),

                Action::make('reject')
                    ->label('Tolak')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
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
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListForgotAttendances::route('/'),
        ];
    }
}
