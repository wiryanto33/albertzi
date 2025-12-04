<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ClientResource\Pages;
use App\Filament\Resources\ClientResource\RelationManagers;
use App\Models\Client;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ClientResource extends Resource
{
    protected static ?string $model = Client::class;
    protected static ?string $navigationIcon = 'heroicon-o-building-office';
    protected static ?string $navigationGroup = 'Master Data';
    protected static ?string $modelLabel = 'Client';
    protected static ?string $pluralModelLabel = 'Client';

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            Forms\Components\Section::make('Identitas')
                ->schema([
                    Forms\Components\TextInput::make('nama')->required()->maxLength(150),
                    Forms\Components\TextInput::make('telepon')->tel()->maxLength(50),
                    Forms\Components\TextInput::make('email')->email()->maxLength(150),
                    Forms\Components\Textarea::make('alamat')->rows(3),
                ])->columns(2),

            // Repeater relasi Projects: input proyek saat Create Client
            Forms\Components\Section::make('Proyek (opsional)')
                ->schema([
                    Forms\Components\Repeater::make('projects')
                        ->relationship('projects')
                        ->schema([
                            Forms\Components\TextInput::make('nama')->required()->maxLength(200),
                            Forms\Components\Textarea::make('lokasi_nama')->label('Lokasi (Nama/Alamat)')->rows(2),
                            Forms\Components\Select::make('status')
                                ->options([
                                    'RENCANA' => 'RENCANA',
                                    'BERJALAN' => 'BERJALAN',
                                    'SELESAI' => 'SELESAI',
                                    'DITUNDA' => 'DITUNDA',
                                ])->required()->preload(),
                            Forms\Components\DatePicker::make('tanggal_mulai'),
                            Forms\Components\DatePicker::make('tanggal_selesai'),
                            Forms\Components\TextInput::make('lat')->numeric()->step('any'),
                            Forms\Components\TextInput::make('lng')->numeric()->step('any'),
                        ])
                        ->defaultItems(0)
                        ->columnSpanFull(),
                ])
                ->columns(1)
                ->visibleOn('create'),
        ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('nama')->searchable()->sortable(),
            Tables\Columns\TextColumn::make('telepon')->toggleable(),
            Tables\Columns\TextColumn::make('email')->toggleable(),
            Tables\Columns\TextColumn::make('projects_count')
                ->counts('projects')->label('Jumlah Proyek')->sortable(),
            Tables\Columns\TextColumn::make('created_at')->dateTime()->since()->toggleable(isToggledHiddenByDefault: true),
        ])
            ->filters([])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()->requiresConfirmation(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListClients::route('/'),
            'create' => Pages\CreateClient::route('/create'),
            'edit' => Pages\EditClient::route('/{record}/edit'),
        ];
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\ProjectsRelationManager::class,
        ];
    }
}
