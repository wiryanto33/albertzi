<?php

namespace App\Filament\Resources\ProjectResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Resources\RelationManagers\RelationManager;

class WorkOrdersRelationManager extends RelationManager
{
    protected static string $relationship = 'workOrders';
    protected static ?string $title = 'Work Orders';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('no_wo')
                ->label('No. WO')
                ->disabled()
                ->dehydrated(false)
                ->placeholder('Otomatis saat dibuat'),
            Forms\Components\Textarea::make('deskripsi')->rows(3),
            Forms\Components\Grid::make(3)->schema([
                Forms\Components\DatePicker::make('tgl_mulai_rencana'),
                Forms\Components\DatePicker::make('tgl_selesai_rencana'),
                Forms\Components\Select::make('status')
                    ->options(['DRAF' => 'DRAF', 'AKTIF' => 'AKTIF', 'SELESAI' => 'SELESAI', 'DIBATALKAN' => 'DIBATALKAN'])
                    ->native(false)->required(),
            ]),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('no_wo')->badge()->weight('bold'),
            Tables\Columns\TextColumn::make('status')->badge()->colors([
                'gray' => 'DRAF',
                'success' => 'AKTIF',
                'primary' => 'SELESAI',
                'danger' => 'DIBATALKAN'
            ]),
            Tables\Columns\TextColumn::make('tgl_mulai_rencana')->date(),
            Tables\Columns\TextColumn::make('tgl_selesai_rencana')->date(),
            Tables\Columns\TextColumn::make('assignments_count')->counts('assignments')->label('Penugasan'),
        ])
            ->headerActions([Tables\Actions\CreateAction::make()])
            ->actions([
                Tables\Actions\Action::make('open')
                    ->label('Buka WO')->url(fn($record) => \App\Filament\Resources\WorkOrderResource::getUrl('edit', ['record' => $record]))
                    ->icon('heroicon-o-arrow-top-right-on-square'),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()->requiresConfirmation(),
            ])
            ->bulkActions([Tables\Actions\DeleteBulkAction::make()]);
    }
}
