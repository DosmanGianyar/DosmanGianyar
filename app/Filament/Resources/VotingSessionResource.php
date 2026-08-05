<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VotingSessionResource\Pages;
use App\Models\VotingSession;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class VotingSessionResource extends Resource
{
    protected static ?string $model = VotingSession::class;

    protected static string|\BackedEnum|null $navigationIcon  = 'heroicon-o-check-badge';
    protected static string|\UnitEnum|null   $navigationGroup = 'E-Voting';
    protected static ?string                 $navigationLabel  = 'Sesi Voting OSIS';
    protected static ?string                 $modelLabel       = 'Sesi Voting';
    protected static ?string                 $pluralModelLabel = 'Sesi E-Voting';

    public static function canAccess(): bool { return auth()->user()?->role === 'admin'; }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Informasi Sesi Pemilihan')
                ->description('Pengaturan nama sesi, jadwal pelaksanaan, dan status E-Voting OSIS')
                ->schema([
                    TextInput::make('title')
                        ->label('Judul Sesi Pemilihan')
                        ->placeholder('Misal: Pemilihan Ketua & Wakil Ketua OSIS SMAN 1 Gianyar 2026/2027')
                        ->required()
                        ->columnSpanFull()
                        ->maxLength(255),

                    Textarea::make('description')
                        ->label('Deskripsi / Petunjuk Pemilih')
                        ->placeholder('Deskripsi singkat mengenai aturan dan petunjuk pencoblosan digital...')
                        ->rows(3)
                        ->columnSpanFull()
                        ->nullable(),

                    DateTimePicker::make('start_time')
                        ->label('Waktu Mulai Voting')
                        ->required()
                        ->native(false),

                    DateTimePicker::make('end_time')
                        ->label('Waktu Selesai Voting')
                        ->required()
                        ->native(false)
                        ->after('start_time'),

                    Select::make('status')
                        ->label('Status Sesi')
                        ->options([
                            'draft'  => 'Draft (Persiapan)',
                            'active' => 'Berlangsung (Buka Voting)',
                            'closed' => 'Selesai (Tutup Voting & Lihat Hasil)',
                        ])
                        ->required()
                        ->default('draft')
                        ->columnSpanFull(),
                ])
                ->columns(2),

            Section::make('Daftar Paslon / Kandidat OSIS (2 Paslon)')
                ->description('Masukkan data lengkap Paslon 01 dan Paslon 02 (Foto, Visi, Misi, Program Kerja, & Video Kampanye)')
                ->schema([
                    Repeater::make('candidates')
                        ->relationship('candidates')
                        ->label('Paslon OSIS')
                        ->schema([
                            TextInput::make('candidate_number')
                                ->label('Nomor Urut Paslon')
                                ->numeric()
                                ->required()
                                ->default(1),

                            TextInput::make('name')
                                ->label('Nama Calon Ketua OSIS')
                                ->placeholder('Misal: I Made Agus Sukarma')
                                ->required(),

                            TextInput::make('vice_name')
                                ->label('Nama Calon Wakil Ketua OSIS')
                                ->placeholder('Misal: Ni Putu Ayu Lestari')
                                ->nullable(),

                            FileUpload::make('photo')
                                ->label('Foto Resmi Paslon (HD)')
                                ->image()
                                ->directory('candidates')
                                ->visibility('public')
                                ->maxSize(5120)
                                ->columnSpanFull(),

                            TextInput::make('motto')
                                ->label('Motto / Slogan Kampanye')
                                ->placeholder('Misal: Bersama Mewujudkan OSIS DOSMAN yang Inovatif dan Berprestasi!')
                                ->columnSpanFull()
                                ->nullable(),

                            Textarea::make('vision')
                                ->label('Visi Paslon')
                                ->rows(2)
                                ->placeholder('Tuliskan visi paslon...')
                                ->columnSpanFull()
                                ->nullable(),

                            Textarea::make('mission')
                                ->label('Misi Paslon (Poin-poin)')
                                ->rows(4)
                                ->placeholder("1. Meningkatkan kedisiplinan dan karakter siswa\n2. Mengembangkan bakat akademik dan non-akademik...")
                                ->columnSpanFull()
                                ->nullable(),

                            Textarea::make('programs')
                                ->label('Program Kerja Unggulan (Proker)')
                                ->rows(3)
                                ->placeholder("1. DOSMAN E-Sports Championship\n2. Gerakan Zero Plastic School\n3. Pentas Seni Digital")
                                ->columnSpanFull()
                                ->nullable(),

                            TextInput::make('video_url')
                                ->label('Link Video Kampanye (Youtube)')
                                ->url()
                                ->placeholder('https://www.youtube.com/watch?v=...')
                                ->columnSpanFull()
                                ->nullable(),
                        ])
                        ->columns(3)
                        ->defaultItems(2)
                        ->minItems(1)
                        ->itemLabel(fn (array $state): ?string => isset($state['candidate_number'], $state['name']) ? "Paslon 0{$state['candidate_number']}: {$state['name']}" . (isset($state['vice_name']) && $state['vice_name'] ? " & {$state['vice_name']}" : "") : 'Paslon OSIS'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('Judul Pemilihan')
                    ->searchable()
                    ->limit(45),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'closed' => 'info',
                        default  => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'active' => '● Berlangsung',
                        'closed' => 'Selesai',
                        default  => 'Draft',
                    }),

                TextColumn::make('start_time')
                    ->label('Waktu Mulai')
                    ->dateTime('d M Y H:i')
                    ->sortable(),

                TextColumn::make('end_time')
                    ->label('Waktu Selesai')
                    ->dateTime('d M Y H:i')
                    ->sortable(),

                TextColumn::make('candidates_count')
                    ->label('Kandidat')
                    ->counts('candidates')
                    ->badge(),

                TextColumn::make('votes_count')
                    ->label('Total Suara')
                    ->counts('votes')
                    ->badge()
                    ->color('primary'),

                TextColumn::make('creator.name')
                    ->label('Dibuat Oleh')
                    ->limit(20),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([ViewAction::make(), EditAction::make()])
            ->bulkActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListVotingSessions::route('/'),
            'create' => Pages\CreateVotingSession::route('/create'),
            'view'   => Pages\ViewVotingSession::route('/{record}'),
            'edit'   => Pages\EditVotingSession::route('/{record}/edit'),
        ];
    }
}
