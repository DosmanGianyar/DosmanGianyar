<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EarlyCheckoutResource\Pages;
use App\Models\EarlyCheckoutRequest;
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

class EarlyCheckoutResource extends Resource
{
    protected static ?string $model = EarlyCheckoutRequest::class;

    protected static string|\BackedEnum|null $navigationIcon  = 'heroicon-o-arrow-left-end-on-rectangle';
    protected static string|\UnitEnum|null   $navigationGroup = 'Presensi Siswa';
    protected static ?string                 $navigationLabel = 'Pulang Lebih Awal';
    protected static ?string                 $modelLabel      = 'Izin Pulang Awal';
    protected static ?string                 $pluralModelLabel = 'Izin Pulang Lebih Awal';
    protected static ?int                    $navigationSort  = 31;

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
                    ->weight('semibold'),

                TextColumn::make('student.schoolClass.name')
                    ->label('Kelas')
                    ->placeholder('—'),

                TextColumn::make('date')
                    ->label('Tanggal')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('requested_time')
                    ->label('Waktu Pulang')
                    ->formatStateUsing(fn (EarlyCheckoutRequest $record) => substr($record->requested_time, 0, 5)),

                TextColumn::make('reason')
                    ->label('Alasan')
                    ->limit(40)
                    ->tooltip(fn (EarlyCheckoutRequest $record) => $record->reason),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state) => match($state) {
                        'pending'  => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        default    => 'gray',
                    })
                    ->formatStateUsing(fn (EarlyCheckoutRequest $record) => $record->statusLabel()),

                TextColumn::make('reviewer.name')
                    ->label('Diproses Oleh')
                    ->placeholder('—')
                    ->toggleable(),

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
                    ->tooltip('Setujui Pengajuan')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->iconButton()
                    ->visible(fn (EarlyCheckoutRequest $record) => $record->isPending())
                    ->form([
                        Textarea::make('reviewer_note')
                            ->label('Catatan (opsional)')
                            ->rows(2),
                    ])
                    ->action(function (EarlyCheckoutRequest $record, array $data): void {
                        $record->update([
                            'status'        => 'approved',
                            'reviewed_by'   => Auth::id(),
                            'reviewed_at'   => now(),
                            'reviewer_note' => $data['reviewer_note'] ?? null,
                        ]);

                        NotificationService::send(
                            userId: $record->student_id,
                            title:  'Izin Pulang Awal Disetujui',
                            body:   'Pengajuan pulang lebih awal tanggal ' . $record->date->isoFormat('D MMMM Y') . ' pukul ' . substr($record->requested_time, 0, 5) . ' telah disetujui.',
                            type:   'success',
                            url:    route('siswa.early-checkout.index'),
                        );

                        if ($record->student) {
                            NotificationService::notifyParentsOfStudent(
                                $record->student,
                                'Izin Pulang Awal Ananda Disetujui',
                                'Pengajuan pulang lebih awal untuk Ananda ' . $record->student->name . ' tanggal ' . $record->date->isoFormat('D MMMM Y') . ' telah disetujui.',
                                'success'
                            );
                        }

                        Notification::make()->title('Pengajuan disetujui')->success()->send();
                    }),

                Action::make('reject')
                    ->label('Tolak')
                    ->tooltip('Tolak Pengajuan')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->iconButton()
                    ->visible(fn (EarlyCheckoutRequest $record) => $record->isPending())
                    ->form([
                        Textarea::make('reviewer_note')
                            ->label('Alasan Penolakan')
                            ->required()
                            ->rows(3),
                    ])
                    ->action(function (EarlyCheckoutRequest $record, array $data): void {
                        $record->update([
                            'status'        => 'rejected',
                            'reviewed_by'   => Auth::id(),
                            'reviewed_at'   => now(),
                            'reviewer_note' => $data['reviewer_note'],
                        ]);

                        NotificationService::send(
                            userId: $record->student_id,
                            title:  'Izin Pulang Awal Ditolak',
                            body:   'Pengajuan pulang lebih awal tanggal ' . $record->date->isoFormat('D MMMM Y') . ' ditolak. Alasan: ' . $data['reviewer_note'],
                            type:   'warning',
                            url:    route('siswa.early-checkout.index'),
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
                        ->modalHeading('Setujui Pengajuan Pulang Awal Terpilih?')
                        ->action(function (Collection $records): void {
                            $approvedCount = 0;
                            foreach ($records as $record) {
                                if ($record->isPending()) {
                                    $record->update([
                                        'status'      => 'approved',
                                        'reviewed_by' => Auth::id(),
                                        'reviewed_at' => now(),
                                    ]);

                                    NotificationService::send(
                                        userId: $record->student_id,
                                        title:  'Izin Pulang Awal Disetujui',
                                        body:   'Pengajuan pulang lebih awal tanggal ' . $record->date->isoFormat('D MMMM Y') . ' pukul ' . substr($record->requested_time, 0, 5) . ' telah disetujui oleh admin.',
                                        type:   'success',
                                        url:    route('siswa.early-checkout.index'),
                                    );

                                    $approvedCount++;
                                }
                            }

                            Notification::make()
                                ->title("{$approvedCount} pengajuan pulang awal berhasil disetujui.")
                                ->success()
                                ->send();
                        }),

                    BulkAction::make('bulk_reject')
                        ->label('Tolak Terpilih')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->form([
                            Textarea::make('reviewer_note')
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
                                        'status'        => 'rejected',
                                        'reviewed_by'   => Auth::id(),
                                        'reviewed_at'   => now(),
                                        'reviewer_note' => $data['reviewer_note'],
                                    ]);

                                    NotificationService::send(
                                        userId: $record->student_id,
                                        title:  'Izin Pulang Awal Ditolak',
                                        body:   'Pengajuan pulang lebih awal tanggal ' . $record->date->isoFormat('D MMMM Y') . ' ditolak. Alasan: ' . $data['reviewer_note'],
                                        type:   'warning',
                                        url:    route('siswa.early-checkout.index'),
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
            'index' => Pages\ListEarlyCheckouts::route('/'),
        ];
    }
}
