<?php

namespace App\Filament\Resources\WorkOrderResource\RelationManagers;

use App\Support\FilamentHelpers;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class DailyReportRelationManager extends RelationManager
{
    protected static string $relationship = 'dailyReports';
    protected static ?string $title = 'Laporan Harian';

    public function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            Forms\Components\Select::make('assignment_id')
                ->relationship('assignment', 'id')->searchable()->required()->native(false)
                ->helperText('Pilih assignment yang sesuai'),
            Forms\Components\DatePicker::make('tanggal')->required(),
            Forms\Components\Grid::make(3)->schema([
                Forms\Components\TextInput::make('progress_persen')->numeric()->minValue(0)->maxValue(100)->suffix('%')->required(),
                Forms\Components\TextInput::make('jam_kerja_operator')->numeric()->step('0.25'),
                Forms\Components\TextInput::make('jam_jalan_alat')->numeric()->step('0.25'),
            ]),
            Forms\Components\Textarea::make('uraian')->rows(3),
            Forms\Components\Grid::make(3)->schema([
                Forms\Components\TextInput::make('lat')->numeric()->step('0.0000001'),
                Forms\Components\TextInput::make('lng')->numeric()->step('0.0000001'),
                Forms\Components\Placeholder::make('map')
                    ->content(function ($record) {
                        return $record ? FilamentHelpers::mapsLink($record->lat, $record->lng) : '—';
                    })->hint('Link Peta'),
            ]),
            Forms\Components\FileUpload::make('photos')
                ->label('Foto Progres')
                ->multiple()
                ->image()
                ->directory('daily-reports')
                ->helperText('Unggah beberapa foto kondisi lapangan')
                ->getUploadedFileNameForStorageUsing(fn($file) => 'dr-' . now()->format('YmdHis') . '-' . $file->getClientOriginalName())
                ->saveRelationshipsUsing(function ($component, $state, $record) {
                    // simpan ke daily_report_photos
                    if (!$record) return;
                    $record->photos()->delete();
                    foreach (($state ?? []) as $path) {
                        $record->photos()->create(['path' => $path, 'caption' => null]);
                    }
                })
                ->dehydrated(false),
        ]);
    }

    public function table(Tables\Table $table): Tables\Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('tanggal')->date()->sortable(),
            Tables\Columns\TextColumn::make('assignment.operator.nama')->label('Operator'),
            Tables\Columns\TextColumn::make('progress_persen')->label('Progres')
                ->formatStateUsing(fn ($state) => $state . '%')
                ->color(fn ($state) => $state >= 80 ? 'success' : ($state >= 50 ? 'warning' : 'danger'))
                ->sortable(),
            Tables\Columns\TextColumn::make('jam_jalan_alat')->label('Jam Alat'),
            Tables\Columns\TextColumn::make('lat')->getStateUsing(fn ($record) => $record->lat ? number_format($record->lat, 6) : null)->toggleable(),
            Tables\Columns\TextColumn::make('lng')->getStateUsing(fn ($record) => $record->lng ? number_format($record->lng, 6) : null)->toggleable(),
            Tables\Columns\IconColumn::make('has_photos')
                ->label('Foto')
                ->boolean()
                ->getStateUsing(fn ($record) => $record->photos()->exists()),
        ])
            ->defaultSort('tanggal', 'desc')
            ->headerActions([Tables\Actions\CreateAction::make()])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()->requiresConfirmation(),
            ]);
    }
}
