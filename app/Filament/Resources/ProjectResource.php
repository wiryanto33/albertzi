<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProjectResource\Pages;
use App\Filament\Resources\ProjectResource\RelationManagers;
use App\Models\Project;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProjectResource extends Resource
{
    protected static ?string $model = Project::class;
    protected static ?string $navigationIcon = 'heroicon-o-map';
    protected static ?string $navigationGroup = 'Master Data';
    protected static ?string $modelLabel = 'Proyek';
    protected static ?string $pluralModelLabel = 'Proyek';

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            Forms\Components\Section::make('Info Proyek')->schema([
                Forms\Components\TextInput::make('nama')->required()->maxLength(200),
                Forms\Components\Select::make('client_id')
                    ->relationship('client', 'nama')->preload()->searchable(),
                Forms\Components\Textarea::make('lokasi_nama')->label('Lokasi (Nama/Alamat)')->rows(2),
            ])->columns(2),

            Forms\Components\Section::make('Waktu & Status')->schema([
                Forms\Components\DatePicker::make('tanggal_mulai'),
                Forms\Components\DatePicker::make('tanggal_selesai'),
                Forms\Components\Select::make('status')
                    ->options([
                        'RENCANA' => 'RENCANA',
                        'BERJALAN' => 'BERJALAN',
                        'SELESAI' => 'SELESAI',
                        'DITUNDA' => 'DITUNDA'
                    ])->preload()->required(),
            ])->columns(3),

            Forms\Components\Section::make('Area Kerja')->schema([
                Forms\Components\TextInput::make('lat')
                    ->numeric()
                    ->step('any')
                    ->reactive(),
                Forms\Components\TextInput::make('lng')
                    ->numeric()
                    ->step('any')
                    ->reactive(),
                Forms\Components\View::make('filament.forms.map-preview')
                    ->reactive()
                    ->columnSpanFull(),
            ])->columns(3),
        ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('nama')->searchable()->weight('semibold'),
            Tables\Columns\TextColumn::make('client.nama')->label('Client')->sortable()->searchable(),
            Tables\Columns\TextColumn::make('status')->badge()->colors([
                'warning' => 'RENCANA',
                'success' => 'BERJALAN',
                'gray'    => 'SELESAI',
                'danger'  => 'DITUNDA',
            ]),
            Tables\Columns\TextColumn::make('tanggal_mulai')->date(),
            Tables\Columns\TextColumn::make('tanggal_selesai')->date(),
            Tables\Columns\TextColumn::make('work_orders_count')->counts('workOrders')->label('Jumlah Pekerja')->sortable(),
        ])
            ->filters([])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()->requiresConfirmation(),
            ])
            ->bulkActions([Tables\Actions\DeleteBulkAction::make()]);
    }

    public static function getRelations(): array
    {
        // Relation managers can be added here. Removed missing WorkOrdersRelationManager reference
        // Relation managers for ProjectResource
        return [
            RelationManagers\WorkOrdersRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProjects::route('/'),
            'create' => Pages\CreateProject::route('/create'),
            'view' => Pages\ViewProject::route('/{record}'),
            'edit' => Pages\EditProject::route('/{record}/edit'),
        ];
    }
}
