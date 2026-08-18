<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LibraryVisitResource\Pages;
use App\Models\LibraryVisit;
use App\Models\SchoolClass;
use App\Models\User;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Support\AdminAccess;
use Filament\Actions\Action;

class LibraryVisitResource extends Resource
{
    protected static ?string $model = LibraryVisit::class;

    protected static string|\BackedEnum|null $navigationIcon  = 'heroicon-o-clipboard-document-check';
    protected static string|\UnitEnum|null   $navigationGroup = 'Perpustakaan';
    protected static ?string                 $navigationLabel = 'Kunjungan (Baca)';
    protected static ?string                 $modelLabel       = 'Kunjungan Perpustakaan';
    protected static ?string                 $pluralModelLabel = 'Kunjungan Perpustakaan (Baca di Tempat)';

    public static function canAccess(): bool { return AdminAccess::can('Perpustakaan'); }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Informasi Kunjungan')->schema([
                Select::make('student_id')
                    ->label('Siswa')
                    ->options(
                        User::where('role', 'siswa')
                            ->orderBy('name')
                            ->get()
                            ->mapWithKeys(fn (User $u) => [
                                $u->id => $u->name . ' (' . ($u->schoolClass?->name ?? '—') . ')',
                            ])
                    )
                    ->searchable()
                    ->required(),

                DateTimePicker::make('visited_at')
                    ->label('Waktu Kunjungan')
                    ->default(now())
                    ->required(),

                TextInput::make('purpose')
                    ->label('Keperluan / Tujuan Membaca')
                    ->default('Membaca Buku Paket / Literasi')
                    ->required(),

                Textarea::make('notes')
                    ->label('Catatan / Judul Buku')
                    ->rows(3)
                    ->columnSpanFull(),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('student.name')
                    ->label('Nama Siswa')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('student.schoolClass.name')
                    ->label('Kelas')
                    ->sortable()
                    ->badge()
                    ->color('info')
                    ->default('—'),

                TextColumn::make('visited_at')
                    ->label('Waktu Kunjungan')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),

                TextColumn::make('purpose')
                    ->label('Keperluan Membaca')
                    ->searchable()
                    ->badge()
                    ->color('success'),

                TextColumn::make('notes')
                    ->label('Catatan / Judul')
                    ->limit(35)
                    ->default('—'),
            ])
            ->defaultSort('visited_at', 'desc')
            ->headerActions([
                Action::make('print_qr_poster')
                    ->label('Cetak QR Code Kunjungan')
                    ->icon('heroicon-o-qr-code')
                    ->color('warning')
                    ->url(fn () => route('admin.library.visit-qr-card'))
                    ->openUrlInNewTab(),
            ])
            ->filters([
                SelectFilter::make('class_id')
                    ->label('Filter Kelas')
                    ->options(fn () => SchoolClass::orderBy('name')->pluck('name', 'id')->toArray())
                    ->query(function (Builder $query, array $data) {
                        if (! empty($data['value'])) {
                            $query->whereHas('student', function (Builder $q) use ($data) {
                                $q->where('school_class_id', $data['value']);
                            });
                        }
                    }),

                SelectFilter::make('purpose')
                    ->label('Keperluan')
                    ->options([
                        'Membaca Buku Paket / Literasi' => 'Membaca Buku Paket / Literasi',
                        'Mengerjakan Tugas / Kliping'  => 'Mengerjakan Tugas / Kliping',
                        'Kerja Kelompok'               => 'Kerja Kelompok',
                        'Mencari Referensi / Jurnal'   => 'Mencari Referensi / Jurnal',
                        'Lainnya'                      => 'Lainnya',
                    ]),
            ])
            ->actions([
                ViewAction::make(),
                DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLibraryVisits::route('/'),
        ];
    }
}
