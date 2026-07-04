<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StudentAchievementResource\Pages;
use App\Models\StudentAchievement;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class StudentAchievementResource extends Resource
{
    protected static ?string $model = StudentAchievement::class;

    protected static string|\BackedEnum|null $navigationIcon       = 'heroicon-o-trophy';
    protected static string|\UnitEnum|null   $navigationGroup      = 'Kesiswaan';
    protected static ?string                 $navigationParentItem = 'Prestasi';
    protected static ?string                 $navigationLabel      = 'Laporan Prestasi';
    protected static ?string                 $modelLabel           = 'Prestasi Siswa';
    protected static ?string                 $pluralModelLabel     = 'Laporan Prestasi';

    public static function canAccess(): bool { return AdminAccess::can('Kesiswaan'); }

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
                TextColumn::make('student.name')
                    ->label('Siswa')
                    ->searchable()
                    ->limit(25),

                TextColumn::make('student.schoolClass.name')
                    ->label('Kelas')
                    ->placeholder('—'),

                TextColumn::make('title')
                    ->label('Judul Prestasi')
                    ->searchable()
                    ->limit(35),

                TextColumn::make('category.name')
                    ->label('Kategori')
                    ->badge()
                    ->color('info')
                    ->placeholder('—'),

                TextColumn::make('level')
                    ->label('Tingkat')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'sekolah'       => 'gray',
                        'kabupaten'     => 'info',
                        'provinsi'      => 'warning',
                        'nasional'      => 'success',
                        'internasional' => 'danger',
                        default         => 'gray',
                    })
                    ->formatStateUsing(fn (StudentAchievement $record): string => $record->levelLabel()),

                TextColumn::make('rank')
                    ->label('Peringkat')
                    ->placeholder('—')
                    ->limit(20),

                TextColumn::make('achievement_date')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (StudentAchievement $record): string => $record->statusColor())
                    ->formatStateUsing(fn (StudentAchievement $record): string => $record->statusLabel()),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'pending'  => 'Menunggu',
                        'approved' => 'Disetujui',
                        'rejected' => 'Ditolak',
                    ]),
                SelectFilter::make('level')
                    ->options([
                        'sekolah'       => 'Sekolah',
                        'kabupaten'     => 'Kabupaten/Kota',
                        'provinsi'      => 'Provinsi',
                        'nasional'      => 'Nasional',
                        'internasional' => 'Internasional',
                    ]),
            ])
            ->actions([
                ViewAction::make(),

                Action::make('approve')
                    ->label('Setujui')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (StudentAchievement $record): bool => $record->status === 'pending')
                    ->requiresConfirmation()
                    ->modalHeading('Setujui Prestasi')
                    ->modalDescription('Prestasi ini akan disetujui dan masuk ke laporan sekolah.')
                    ->action(function (StudentAchievement $record): void {
                        $record->update([
                            'status'      => 'approved',
                            'verified_by' => auth()->id(),
                            'verified_at' => now(),
                            'rejection_reason' => null,
                        ]);
                        Notification::make()->title('Prestasi disetujui')->success()->send();
                    }),

                Action::make('reject')
                    ->label('Tolak')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (StudentAchievement $record): bool => $record->status === 'pending')
                    ->form([
                        Textarea::make('rejection_reason')
                            ->label('Alasan Penolakan')
                            ->required()
                            ->rows(3),
                    ])
                    ->action(function (StudentAchievement $record, array $data): void {
                        $record->update([
                            'status'           => 'rejected',
                            'rejection_reason' => $data['rejection_reason'],
                            'verified_by'      => auth()->id(),
                            'verified_at'      => now(),
                        ]);
                        Notification::make()->title('Prestasi ditolak')->danger()->send();
                    }),
            ])
            ->bulkActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStudentAchievements::route('/'),
            'view'  => Pages\ViewStudentAchievement::route('/{record}'),
        ];
    }
}
