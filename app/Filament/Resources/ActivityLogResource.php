<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ActivityLogResource\Pages;
use App\Models\ActivityLog;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ActivityLogResource extends Resource
{
    protected static ?string $model = ActivityLog::class;

    protected static string|\BackedEnum|null $navigationIcon  = 'heroicon-o-clipboard-document-list';
    protected static string|\UnitEnum|null   $navigationGroup = 'Sistem';
    protected static ?string                 $navigationLabel  = 'Audit Log';
    protected static ?string                 $modelLabel       = 'Log Aktivitas';
    protected static ?string                 $pluralModelLabel = 'Audit Log';

    public static function canAccess(): bool { return auth()->user()?->role === 'admin'; }

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
                TextColumn::make('created_at')
                    ->label('Waktu')
                    ->dateTime('d M Y H:i:s')
                    ->sortable(),

                TextColumn::make('user.name')
                    ->label('User')
                    ->searchable()
                    ->limit(25),

                TextColumn::make('user.role')
                    ->label('Role')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'admin'          => 'danger',
                        'guru'           => 'warning',
                        'pengelola'=> 'info',
                        default          => 'gray',
                    }),

                TextColumn::make('action')
                    ->label('Aksi')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'login'   => 'success',
                        'logout'  => 'gray',
                        'import'  => 'warning',
                        'deleted' => 'danger',
                        default   => 'info',
                    }),

                TextColumn::make('subject_type')
                    ->label('Objek')
                    ->formatStateUsing(fn (?string $state) => $state ? class_basename($state) : '—')
                    ->limit(20),

                TextColumn::make('ip_address')
                    ->label('IP')
                    ->limit(20),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('action')
                    ->options([
                        'login'  => 'Login',
                        'logout' => 'Logout',
                        'import' => 'Import',
                    ]),
            ])
            ->paginated([25, 50, 100]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListActivityLogs::route('/'),
        ];
    }
}
