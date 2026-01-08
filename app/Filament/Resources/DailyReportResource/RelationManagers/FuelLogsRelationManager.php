<?php

namespace App\Filament\Resources\DailyReportResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class FuelLogsRelationManager extends RelationManager
{
    protected static string $relationship = 'fuelLogs';
    protected static ?string $title = 'Log BBM';
    protected static ?string $recordTitleAttribute = 'tanggal';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\DatePicker::make('tanggal')
                ->label('Tanggal')
                ->default(now())
                ->required(),

            Forms\Components\TextInput::make('liter')
                ->label('Liter')
                ->numeric()
                ->step('0.01')
                ->suffix('L')
                ->required(),

            Forms\Components\TextInput::make('odometer_jam_awal')
                ->label('Odometer/Jam Awal')
                ->numeric(),

            Forms\Components\TextInput::make('odometer_jam_akhir')
                ->label('Odometer/Jam Akhir')
                ->numeric(),

            Forms\Components\FileUpload::make('bukti_foto')
                ->label('Bukti Foto')
                ->image()
                ->directory('bbm')
                ->maxSize(5120),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('tanggal')
            ->columns([
                Tables\Columns\TextColumn::make('tanggal')
                    ->label('Tanggal')
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('liter')
                    ->label('Liter')
                    ->suffix(' L')
                    ->numeric(2)
                    ->sortable(),

                Tables\Columns\TextColumn::make('odometer_jam_awal')
                    ->label('Jam Awal')
                    ->numeric(),

                Tables\Columns\TextColumn::make('odometer_jam_akhir')
                    ->label('Jam Akhir')
                    ->numeric(),
            ])
            ->defaultSort('tanggal', 'desc')
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        // Auto-assign assignment_id dari daily report
                        $data['assignment_id'] = $this->getOwnerRecord()->assignment_id;
                        return $data;
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
