<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ExtracurricularResource\Pages;
use App\Filament\Resources\ExtracurricularResource\RelationManagers;
use App\Models\Extracurricular;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use App\Filament\Support\AdminAccess;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Filament\Tables\Table;

class ExtracurricularResource extends Resource
{
    protected static ?string $model = Extracurricular::class;

    protected static string|\BackedEnum|null $navigationIcon  = 'heroicon-o-academic-cap';
    protected static string|\UnitEnum|null   $navigationGroup = 'Prestasi & Ekskul';
    protected static ?string                 $navigationLabel = 'Ekstrakurikuler';
    protected static ?string                 $modelLabel      = 'Ekstrakurikuler';
    protected static ?string                 $pluralModelLabel = 'Ekstrakurikuler';
    protected static ?int                    $navigationSort  = 10;

    public static function canAccess(): bool
    {
        if (AdminAccess::can('Prestasi & Ekskul')) {
            return true;
        }
        $user = Auth::user();
        return (bool) ($user?->isGuru() && $user->isPembinaEkstra());
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')
                ->label('Nama Ekstrakurikuler')
                ->required()
                ->maxLength(100)
                ->columnSpanFull(),

            Textarea::make('description')
                ->label('Deskripsi')
                ->rows(3)
                ->nullable()
                ->columnSpanFull(),

            Select::make('teachers')
                ->label('Guru Pembina (Bisa lebih dari 1)')
                ->relationship('teachers', 'name', fn ($query) => $query->where('role', 'guru')->orderBy('name'))
                ->multiple()
                ->preload()
                ->searchable()
                ->placeholder('— Pilih satu atau beberapa guru pembina —')
                ->columnSpanFull(),

            TextInput::make('max_members')
                ->label('Kuota Anggota')
                ->numeric()
                ->minValue(1)
                ->nullable()
                ->placeholder('Kosongkan = tidak terbatas')
                ->helperText('Maks. anggota aktif yang dapat diterima'),

            Toggle::make('is_active')
                ->label('Aktif')
                ->default(true)
                ->helperText('Nonaktifkan agar tidak muncul di aplikasi siswa'),

            FileUpload::make('logo')
                ->label('Logo / Foto')
                ->image()
                ->directory('extracurriculars')
                ->nullable()
                ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('no_urut')
                    ->label('No.')
                    ->rowIndex()
                    ->alignCenter()
                    ->width('48px'),

                ImageColumn::make('logo')
                    ->label('')
                    ->disk('public')
                    ->width(44)
                    ->height(44)
                    ->rounded()
                    ->defaultImageUrl(asset('img/logo_sekolah.png')),

                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->weight('semibold')
                    ->sortable(),

                TextColumn::make('pembina_names')
                    ->label('Guru Pembina')
                    ->placeholder('—')
                    ->wrap()
                    ->searchable(query: function ($query, string $search) {
                        $query->whereHas('teachers', fn ($q) => $q->where('name', 'like', "%{$search}%"))
                              ->orWhereHas('pembina', fn ($q) => $q->where('name', 'like', "%{$search}%"));
                    }),

                TextColumn::make('active_members_count')
                    ->label('Anggota')
                    ->counts('activeMembers')
                    ->weight('bold')
                    ->color('success')
                    ->alignCenter(),

                TextColumn::make('pending_members_count')
                    ->label('Permintaan')
                    ->counts('pendingMembers')
                    ->weight('bold')
                    ->color(fn (int $state): string => $state > 0 ? 'warning' : 'gray')
                    ->alignCenter(),

                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
            ])
            ->filters([
                TernaryFilter::make('is_active')->label('Status'),
            ])
            ->headerActions([
                Action::make('cetak_per_ekstra')
                    ->label('Cetak Per Ekstra')
                    ->icon('heroicon-o-document-text')
                    ->color('success')
                    ->form([
                        Select::make('extracurricular_id')
                            ->label('Pilih Ekstrakurikuler')
                            ->options(Extracurricular::orderBy('name')->pluck('name', 'id'))
                            ->searchable()
                            ->required(),
                    ])
                    ->action(function (array $data) {
                        $url = route('admin.extracurricular.members.pdf', $data['extracurricular_id']);
                        return redirect()->away($url);
                    }),

                Action::make('cetak_tanpa_ekstra')
                    ->label('Siswa Tanpa Ekstra')
                    ->icon('heroicon-o-user-minus')
                    ->color('danger')
                    ->url(fn () => route('admin.extracurricular.no-ekstra.pdf'))
                    ->openUrlInNewTab(),
            ])
            ->actions([
                Action::make('cetak_anggota')
                    ->label('Cetak Anggota')
                    ->tooltip('Cetak Anggota Ekstra')
                    ->icon('heroicon-o-printer')
                    ->color('success')
                    ->iconButton()
                    ->url(fn (Extracurricular $record) => route('admin.extracurricular.members.pdf', $record))
                    ->openUrlInNewTab(),
                EditAction::make()
                    ->label('Edit')
                    ->tooltip('Edit Ekstrakurikuler')
                    ->iconButton(),
                DeleteAction::make()
                    ->label('Hapus')
                    ->tooltip('Hapus Ekstrakurikuler')
                    ->iconButton(),
            ])
            ->defaultSort('name');
    }

    public static function getRelationManagers(): array
    {
        return [
            RelationManagers\MembersRelationManager::class,
            RelationManagers\SessionsRelationManager::class,
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user  = Auth::user();

        if ($user?->isGuru() && ! AdminAccess::can('Prestasi & Ekskul')) {
            $query->where(function ($q) use ($user) {
                $q->where('pembina_id', $user->id)
                  ->orWhereHas('teachers', fn ($t) => $t->where('users.id', $user->id));
            });
        }

        return $query;
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListExtracurriculars::route('/'),
            'create' => Pages\CreateExtracurricular::route('/create'),
            'edit'   => Pages\EditExtracurricular::route('/{record}/edit'),
        ];
    }
}
