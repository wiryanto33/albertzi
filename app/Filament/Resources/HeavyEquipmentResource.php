<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HeavyEquipmentResource\Pages;
use App\Filament\Resources\HeavyEquipmentResource\RelationManagers;
use App\Models\HeavyEquipment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class HeavyEquipmentResource extends Resource
{
    protected static ?string $model = HeavyEquipment::class;
    protected static ?string $navigationIcon = 'heroicon-o-truck';
    protected static ?string $navigationGroup = 'Master Data';
    protected static ?string $modelLabel = 'Alat Berat';
    protected static ?string $pluralModelLabel = 'Alat Berat';

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            Forms\Components\Section::make('Identitas Alat')
                ->schema([
                    Forms\Components\TextInput::make('kode')->required()->unique(ignoreRecord: true),
                    Forms\Components\TextInput::make('nama')->required(),
                    Forms\Components\TextInput::make('tipe'),
                    Forms\Components\TextInput::make('nopol'),
                    Forms\Components\TextInput::make('tahun')->numeric()->minValue(1970)->maxValue((int)date('Y') + 1),
                ])->columns(2),

            Forms\Components\Section::make('Kondisi & Servis')
                ->schema([
                    Forms\Components\Select::make('status_kesiapan')
                        ->options([
                            'SIAP' => 'SIAP',
                            'TIDAK_SIAP' => 'TIDAK_SIAP',
                            'PERBAIKAN' => 'PERBAIKAN',
                        ])->required()->native(false),
                    Forms\Components\TextInput::make('jam_jalan_total')->label('Jam Putar')->numeric()->default(0),
                    Forms\Components\DatePicker::make('last_service_at')->label('Tanggal Servis Terakhir'),
                ])->columns(3),
        ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('kode')->badge()->color('gray')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('nama')->weight('bold')->searchable(),
                Tables\Columns\TextColumn::make('tipe')->toggleable(),
                Tables\Columns\TextColumn::make('nopol')->toggleable(),
                Tables\Columns\TextColumn::make('status_kesiapan')->badge()
                    ->colors([
                        'success' => 'SIAP',
                        'danger'  => 'TIDAK_SIAP',
                        'warning' => 'PERBAIKAN',
                    ])->sortable(),
                Tables\Columns\TextColumn::make('jam_jalan_total')->label('Jam Jalan')->sortable(),
                Tables\Columns\TextColumn::make('last_service_at')->date()->label('Servis Terakhir')->toggleable(),
            ])
            ->defaultSort('kode')
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()->requiresConfirmation(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListHeavyEquipment::route('/'),
            'create' => Pages\CreateHeavyEquipment::route('/create'),
            'edit' => Pages\EditHeavyEquipment::route('/{record}/edit'),
        ];
    }
}
