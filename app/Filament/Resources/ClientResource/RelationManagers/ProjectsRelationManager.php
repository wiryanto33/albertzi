<?php

namespace App\Filament\Resources\ClientResource\RelationManagers;

use App\Filament\Resources\ProjectResource;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProjectsRelationManager extends RelationManager
{
    protected static string $relationship = 'projects';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Info Proyek')->schema([
                Forms\Components\TextInput::make('nama')->required()->maxLength(200),
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

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('nama')
            ->columns([
                Tables\Columns\TextColumn::make('nama')->searchable()->weight('semibold'),
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
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->url(fn ($record): string => ProjectResource::getUrl('view', ['record' => $record])),
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
