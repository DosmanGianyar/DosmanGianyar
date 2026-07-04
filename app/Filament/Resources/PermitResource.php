<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PermitResource\Pages;
use App\Models\Attendance;
use App\Models\Permit;
use App\Services\NotificationService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class PermitResource extends Resource
{
    protected static ?string $model = Permit::class;

    protected static string|\BackedEnum|null $navigationIcon  = 'heroicon-o-document-check';
    protected static string|\UnitEnum|null   $navigationGroup = 'Kesiswaan';
    protected static ?string                 $navigationLabel = 'Izin / Sakit / Dispen';
    protected static ?string                 $modelLabel      = 'Pengajuan Izin';
    protected static ?string                 $pluralModelLabel = 'Izin / Sakit / Dispensasi';
    protected static ?int                    $navigationSort  = 30;

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
                    ->placeholder('—')
                    ->sortable(),

                TextColumn::make('type')
                    ->label('Jenis')
                    ->badge()
                    ->color(fn (string $state) => match($state) {
                        'izin'       => 'info',
                        'sakit'      => 'warning',
                        'dispensasi' => 'gray',
                        default      => 'gray',
                    })
                    ->formatStateUsing(fn (Permit $record) => $record->typeLabel()),

                TextColumn::make('start_date')
                    ->label('Tanggal')
                    ->formatStateUsing(fn (Permit $record) => $record->start_date->isoFormat('D MMM Y') .
                        ($record->start_date->eq($record->end_date) ? '' : ' – ' . $record->end_date->isoFormat('D MMM Y')))
                    ->sortable(),

                TextColumn::make('reason')
                    ->label('Alasan')
                    ->limit(40)
                    ->tooltip(fn (Permit $record) => $record->reason),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state) => match($state) {
                        'pending'  => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        default    => 'gray',
                    })
                    ->formatStateUsing(fn (string $state) => match($state) {
                        'pending'  => 'Menunggu',
                        'approved' => 'Disetujui',
                        'rejected' => 'Ditolak',
                        default    => ucfirst($state),
                    }),

                TextColumn::make('created_at')
                    ->label('Diajukan')
                    ->since()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'pending'  => 'Menunggu',
                        'approved' => 'Disetujui',
                        'rejected' => 'Ditolak',
                    ])
                    ->default('pending'),

                SelectFilter::make('type')
                    ->label('Jenis')
                    ->options([
                        'izin'       => 'Izin',
                        'sakit'      => 'Sakit',
                        'dispensasi' => 'Dispensasi',
                    ]),
            ])
            ->recordActions([
                Action::make('approve')
                    ->label('Setujui')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (Permit $record) => $record->isPending())
                    ->requiresConfirmation()
                    ->modalHeading('Setujui Pengajuan')
                    ->modalDescription(fn (Permit $record) => "Setujui {$record->typeLabel()} siswa {$record->student?->name}?")
                    ->action(function (Permit $record): void {
                        $record->update([
                            'status'      => 'approved',
                            'approved_by' => Auth::id(),
                        ]);

                        // Sync absensi
                        $current = $record->start_date->copy();
                        while ($current->lte($record->end_date)) {
                            if ($current->isWeekday()) {
                                Attendance::updateOrCreate(
                                    ['user_id' => $record->student_id, 'date' => $current->toDateString()],
                                    ['status' => $record->type, 'check_in_time' => null]
                                );
                            }
                            $current->addDay();
                        }

                        NotificationService::send(
                            $record->student_id,
                            "{$record->typeLabel()} Disetujui",
                            "Pengajuan {$record->typeLabel()} kamu untuk tanggal {$record->start_date->isoFormat('D MMM Y')} telah disetujui.",
                            'success',
                            route('siswa.permit.index'),
                        );

                        Notification::make()->title('Pengajuan disetujui')->success()->send();
                    }),

                Action::make('reject')
                    ->label('Tolak')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (Permit $record) => $record->isPending())
                    ->form([
                        Textarea::make('rejection_note')
                            ->label('Alasan Penolakan')
                            ->required()
                            ->rows(3),
                    ])
                    ->action(function (Permit $record, array $data): void {
                        $record->update([
                            'status'         => 'rejected',
                            'approved_by'    => Auth::id(),
                            'rejection_note' => $data['rejection_note'],
                        ]);

                        NotificationService::send(
                            $record->student_id,
                            "{$record->typeLabel()} Ditolak",
                            "Pengajuan {$record->typeLabel()} kamu ditolak. Alasan: {$data['rejection_note']}",
                            'warning',
                            route('siswa.permit.index'),
                        );

                        Notification::make()->title('Pengajuan ditolak')->danger()->send();
                    }),
            ])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPermits::route('/'),
        ];
    }
}
