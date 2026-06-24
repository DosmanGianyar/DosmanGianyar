<?php

namespace App\Filament\Resources\ExtracurricularResource\RelationManagers;

use App\Exports\ExtracurricularAttendanceExport;
use App\Models\ExtracurricularSession;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Actions\Action as TableAction;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class SessionsRelationManager extends RelationManager
{
    protected static string $relationship = 'sessions';
    protected static ?string $title = 'Sesi Absensi';
    protected static string|\BackedEnum|null $icon = 'heroicon-o-calendar-days';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('title')
                ->label('Judul Sesi')
                ->required()
                ->maxLength(120)
                ->columnSpanFull(),

            DatePicker::make('session_date')
                ->label('Tanggal')
                ->native(false)
                ->required(),

            TimePicker::make('start_time')
                ->label('Mulai')
                ->required()
                ->seconds(false),

            TimePicker::make('end_time')
                ->label('Selesai')
                ->required()
                ->seconds(false)
                ->after('start_time'),

            TextInput::make('location')
                ->label('Lokasi')
                ->nullable()
                ->maxLength(100)
                ->columnSpanFull(),

            Textarea::make('notes')
                ->label('Catatan')
                ->rows(2)
                ->nullable()
                ->columnSpanFull(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                TextColumn::make('session_date')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('title')
                    ->label('Judul Sesi')
                    ->weight('semibold')
                    ->limit(40),

                TextColumn::make('start_time')
                    ->label('Waktu')
                    ->formatStateUsing(fn (ExtracurricularSession $r) =>
                        substr($r->start_time, 0, 5) . ' – ' . substr($r->end_time, 0, 5)
                    ),

                TextColumn::make('location')
                    ->label('Lokasi')
                    ->placeholder('—')
                    ->limit(25),

                TextColumn::make('hadir_count')
                    ->label('Hadir')
                    ->getStateUsing(fn (ExtracurricularSession $r) => $r->hadirCount())
                    ->badge()
                    ->color('success'),

                TextColumn::make('alpa_count')
                    ->label('Alpa')
                    ->getStateUsing(fn (ExtracurricularSession $r) => $r->alpaCount())
                    ->badge()
                    ->color('danger'),

                IconColumn::make('is_open')
                    ->label('Absen')
                    ->boolean()
                    ->trueIcon('heroicon-o-lock-open')
                    ->falseIcon('heroicon-o-lock-closed')
                    ->trueColor('success')
                    ->falseColor('gray'),
            ])
            ->headerActions([
                CreateAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['created_by'] = auth()->id();
                        return $data;
                    }),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),

                TableAction::make('toggle_open')
                    ->label(fn (ExtracurricularSession $r) => $r->is_open ? 'Tutup Absen' : 'Buka Absen')
                    ->icon(fn (ExtracurricularSession $r) => $r->is_open ? 'heroicon-o-lock-closed' : 'heroicon-o-lock-open')
                    ->color(fn (ExtracurricularSession $r) => $r->is_open ? 'danger' : 'success')
                    ->action(function (ExtracurricularSession $r) {
                        $r->update(['is_open' => !$r->is_open]);
                        $msg = $r->is_open ? 'Absen dibuka.' : 'Absen ditutup.';
                        Notification::make()->title($msg)->success()->send();
                    }),

                TableAction::make('export_excel')
                    ->label('Excel')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('success')
                    ->action(function (ExtracurricularSession $r): BinaryFileResponse {
                        return Excel::download(
                            new ExtracurricularAttendanceExport($r),
                            'rekap-ekstra-' . $r->session_date->format('Ymd') . '.xlsx'
                        );
                    }),

                TableAction::make('export_pdf')
                    ->label('PDF')
                    ->icon('heroicon-o-document-text')
                    ->color('danger')
                    ->url(fn (ExtracurricularSession $r) =>
                        route('admin.extracurricular.session.pdf', $r->id)
                    )
                    ->openUrlInNewTab(),
            ])
            ->defaultSort('session_date', 'desc');
    }
}
