<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VotingSessionResource\Pages;
use App\Models\VotingSession;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class VotingSessionResource extends Resource
{
    protected static ?string $model = VotingSession::class;

    protected static string|\BackedEnum|null $navigationIcon  = 'heroicon-o-check-badge';
    protected static string|\UnitEnum|null   $navigationGroup = 'E-Voting';
    protected static ?string                 $navigationLabel  = 'Sesi Voting';
    protected static ?string                 $modelLabel       = 'Sesi Voting';
    protected static ?string                 $pluralModelLabel = 'Sesi E-Voting';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('title')
                ->label('Judul Voting')
                ->required()
                ->maxLength(255),

            Textarea::make('description')
                ->label('Deskripsi')
                ->rows(3)
                ->nullable(),

            DateTimePicker::make('start_time')
                ->label('Waktu Mulai')
                ->required()
                ->native(false),

            DateTimePicker::make('end_time')
                ->label('Waktu Selesai')
                ->required()
                ->native(false)
                ->after('start_time'),

            Select::make('status')
                ->label('Status')
                ->options([
                    'draft'  => 'Draft',
                    'active' => 'Berlangsung',
                    'closed' => 'Selesai',
                ])
                ->required()
                ->default('draft'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('Judul')
                    ->searchable()
                    ->limit(40),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'closed' => 'info',
                        default  => 'gray',
                    }),

                TextColumn::make('start_time')
                    ->label('Mulai')
                    ->dateTime('d M Y H:i')
                    ->sortable(),

                TextColumn::make('end_time')
                    ->label('Selesai')
                    ->dateTime('d M Y H:i')
                    ->sortable(),

                TextColumn::make('candidates_count')
                    ->label('Kandidat')
                    ->counts('candidates'),

                TextColumn::make('votes_count')
                    ->label('Suara')
                    ->counts('votes'),

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
