<?php

namespace App\Filament\Resources\WorkOrderResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class IncidentReportRelationManager extends RelationManager
{
    protected static string $relationship = 'incidentReports';
    protected static ?string $title = 'Insiden/Kerusakan';

    public function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            Forms\Components\Select::make('assignment_id')
                ->relationship('assignment', 'id')->searchable()->required()->native(false),
            Forms\Components\DateTimePicker::make('tanggal')->required(),
            Forms\Components\TextInput::make('kategori')->required(),
            Forms\Components\Select::make('severity')->options([
                'LOW' => 'LOW',
                'MED' => 'MED',
                'HIGH' => 'HIGH'
            ])->native(false),
            Forms\Components\Textarea::make('deskripsi')->rows(3),
            Forms\Components\FileUpload::make('foto_bukti')->image()->directory('incidents'),
        ]);
    }

    public function table(Tables\Table $table): Tables\Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('tanggal')->dateTime()->sortable(),
            Tables\Columns\TextColumn::make('assignment.operator.nama')->label('Operator'),
            Tables\Columns\TextColumn::make('kategori')->badge(),
            Tables\Columns\TextColumn::make('severity')->badge()->colors([
                'success' => 'LOW',
                'warning' => 'MED',
                'danger' => 'HIGH'
            ]),
        ])
            ->headerActions([Tables\Actions\CreateAction::make()])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()->requiresConfirmation(),
            ]);
    }
}
