<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WorkOrderResource\Pages;
use App\Filament\Resources\WorkOrderResource\RelationManagers;
use App\Filament\Resources\WorkOrderResource\RelationManagers\AssignmentsRelationManager;
use App\Filament\Resources\WorkOrderResource\RelationManagers\DailyReportRelationManager;
use App\Filament\Resources\WorkOrderResource\RelationManagers\IncidentReportRelationManager;
use App\Models\WorkOrder;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class WorkOrderResource extends Resource
{
    protected static ?string $model = WorkOrder::class;
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationGroup = 'Master Data';
    protected static ?string $modelLabel = 'Perintah Kerja';
    protected static ?string $pluralModelLabel = 'Perintah Kerja';

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            Forms\Components\Section::make('WO')->schema([
                Forms\Components\Select::make('project_id')
                    ->relationship('project', 'nama')->preload()->searchable()->required(),
                Forms\Components\TextInput::make('no_wo')
                    ->label('No. WO')
                    ->disabled()
                    ->dehydrated(false)
                    ->placeholder('Otomatis saat dibuat'),
                Forms\Components\Textarea::make('deskripsi')->rows(3),
            ])->columns(2),
            Forms\Components\Section::make('Jadwal & Status')->schema([
                Forms\Components\DatePicker::make('tgl_mulai_rencana'),
                Forms\Components\DatePicker::make('tgl_selesai_rencana'),
                Forms\Components\Select::make('status')->options([
                    'DRAF' => 'DRAF',
                    'AKTIF' => 'AKTIF',
                    'SELESAI' => 'SELESAI',
                    'DIBATALKAN' => 'DIBATALKAN'
                ])->required(),
            ])->columns(3),
        ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('no_wo')->label('No SP') ->badge()->weight('bold')->searchable(),
            Tables\Columns\TextColumn::make('project.nama')->label('Proyek')->sortable()->searchable(),
            Tables\Columns\TextColumn::make('status')->badge()->colors([
                'gray' => 'DRAF',
                'success' => 'AKTIF',
                'primary' => 'SELESAI',
                'danger' => 'DIBATALKAN'
            ])->sortable(),
            Tables\Columns\TextColumn::make('assignments_count')->counts('assignments')->label('Penugasan')->sortable(),
            Tables\Columns\TextColumn::make('daily_reports_count')->counts('dailyReports')->label('Laporan')->sortable(),
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
        return [
            AssignmentsRelationManager::class,
            DailyReportRelationManager::class,
            IncidentReportRelationManager::class,
            // DocumentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWorkOrders::route('/'),
            'create' => Pages\CreateWorkOrder::route('/create'),
            'view' => Pages\ViewWorkOrder::route('/{record}'),
            'edit' => Pages\EditWorkOrder::route('/{record}/edit'),
        ];
    }
}
