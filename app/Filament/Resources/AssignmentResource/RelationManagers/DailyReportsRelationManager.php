<?php

namespace App\Filament\Resources\AssignmentResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class DailyReportsRelationManager extends RelationManager
{
    protected static string $relationship = 'dailyReports';
    protected static ?string $title = 'Laporan Harian';

    public function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            Forms\Components\DatePicker::make('tanggal')->required(),
            Forms\Components\TextInput::make('progress_persen')->numeric()->minValue(0)->maxValue(100)->suffix('%')->required(),
            Forms\Components\TextInput::make('jam_kerja_operator')->numeric()->step('0.25'),
            Forms\Components\TextInput::make('jam_jalan_alat')->numeric()->step('0.25'),
            Forms\Components\Textarea::make('uraian')->rows(3),
            Forms\Components\Section::make('Dokumentasi Foto')->schema([
                Forms\Components\Repeater::make('photos')
                    ->relationship('photos')
                    ->schema([
                        Forms\Components\FileUpload::make('path')->image()->directory('daily-reports')->required(),
                        Forms\Components\TextInput::make('caption')->label('Keterangan'),
                    ])->columns(2)->minItems(0)
                    ->addActionLabel('Tambah Foto')
                    ->collapsed(false),
            ])->collapsible(),
            Forms\Components\Grid::make(3)->schema([
                Forms\Components\TextInput::make('lat')->numeric()->step('0.0000001'),
                Forms\Components\TextInput::make('lng')->numeric()->step('0.0000001'),
                Forms\Components\TextInput::make('cuaca'),
            ]),
        ]);
    }

    public function table(Tables\Table $table): Tables\Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('tanggal')->date(),
            Tables\Columns\TextColumn::make('progress_persen')->label('Progres')
                ->formatStateUsing(fn($state) => $state . '%')
                ->color(fn($state) => $state >= 80 ? 'success' : ($state >= 50 ? 'warning' : 'danger')),
            Tables\Columns\TextColumn::make('jam_jalan_alat')->label('Jam Alat'),
            Tables\Columns\TextColumn::make('created_at')->since(),
        ])
            ->headerActions([Tables\Actions\CreateAction::make()])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->modalContent(fn($record) => view('filament.components.daily-report-map', [
                        'record' => $record,
                    ]))
                    ->modalWidth('3xl'),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()->requiresConfirmation(),
            ]);
    }
}
