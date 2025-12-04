<?php

namespace App\Filament\Resources\WorkOrderResource\RelationManagers;

use Filament\Forms;
use Filament\Tables;
use Filament\Resources\RelationManagers\RelationManager;

class AssignmentsRelationManager extends RelationManager
{
    protected static string $relationship = 'assignments';
    protected static ?string $title = 'Penugasan (Alat & Operator)';

    public function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            Forms\Components\Select::make('heavy_equipment_id')
                ->relationship('heavyEquipment', 'nama')->searchable()->required()->native(false),
            Forms\Components\Select::make('operator_id')
                ->relationship('operator', 'nama')->searchable()->required()->native(false),
            Forms\Components\TextInput::make('shift'),
            Forms\Components\Grid::make(3)->schema([
                Forms\Components\DatePicker::make('tgl_mulai'),
                Forms\Components\DatePicker::make('tgl_selesai'),
                Forms\Components\Select::make('status')->options([
                    'AKTIF' => 'AKTIF',
                    'SELESAI' => 'SELESAI',
                    'DIBATALKAN' => 'DIBATALKAN'
                ])->native(false)->required(),
            ]),
        ]);
    }

    public function table(Tables\Table $table): Tables\Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('heavyEquipment.nama')->label('Alat')->searchable(),
            Tables\Columns\TextColumn::make('operator.nama')->label('Operator')->searchable(),
            Tables\Columns\TextColumn::make('shift')->toggleable(),
            Tables\Columns\TextColumn::make('status')->badge()->colors([
                'success' => 'AKTIF',
                'primary' => 'SELESAI',
                'danger' => 'DIBATALKAN'
            ]),
            Tables\Columns\TextColumn::make('tgl_mulai')->date(),
            Tables\Columns\TextColumn::make('tgl_selesai')->date(),
        ])
            ->headerActions([Tables\Actions\CreateAction::make()])
            ->actions([
                Tables\Actions\Action::make('open')
                    ->label('Buka Penugasan')
                    ->url(fn($record) => \App\Filament\Resources\AssignmentResource::getUrl('edit', ['record' => $record]))
                    ->icon('heroicon-o-arrow-top-right-on-square'),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()->requiresConfirmation(),
            ]);
    }
}

