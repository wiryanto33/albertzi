<?php

namespace App\Filament\Resources\AssignmentResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class FuelLogsRelationManager extends RelationManager
{
    protected static string $relationship = 'fuelLogs';
    protected static ?string $title = 'BBM';

    public function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            Forms\Components\DatePicker::make('tanggal')->required(),
            Forms\Components\TextInput::make('liter')->numeric()->step('0.01')->required(),
            Forms\Components\TextInput::make('odometer_jam_awal')->numeric(),
            Forms\Components\TextInput::make('odometer_jam_akhir')->numeric(),
            Forms\Components\FileUpload::make('bukti_foto')->image()->directory('bbm'),
        ]);
    }

    public function table(Tables\Table $table): Tables\Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('tanggal')->date(),
            Tables\Columns\TextColumn::make('liter')->suffix(' L')->numeric(2)->sortable(),
            Tables\Columns\TextColumn::make('odometer_jam_awal')->label('Jam Awal')->toggleable(),
            Tables\Columns\TextColumn::make('odometer_jam_akhir')->label('Jam Akhir')->toggleable(),
        ])
            ->headerActions([Tables\Actions\CreateAction::make()])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()->requiresConfirmation(),
            ]);
    }
}
