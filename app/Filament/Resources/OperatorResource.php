<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OperatorResource\Pages;
use App\Filament\Resources\OperatorResource\RelationManagers;
use App\Models\Operator;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class OperatorResource extends Resource
{
    protected static ?string $model = Operator::class;
    protected static ?string $navigationIcon = 'heroicon-o-user';
    protected static ?string $navigationGroup = 'Master Data';
    protected static ?string $modelLabel = 'Operator';
    protected static ?string $pluralModelLabel = 'Operator';

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            Forms\Components\Select::make('user_id')
                ->label('Jabatan')
                ->relationship('user', 'name')
                ->searchable()
                ->preload(),
            Forms\Components\TextInput::make('nama')->required(),
            Forms\Components\TextInput::make('pangkat'),
            Forms\Components\TextInput::make('nrp'),
            Forms\Components\TextInput::make('sertifikasi')->label('Sertifikasi/Keahlian'),
            Forms\Components\Toggle::make('aktif')->default(true),
        ])->columns(2);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('nama')->searchable()->sortable()->weight('semibold'),
            Tables\Columns\TextColumn::make('user.name')->label('User')->toggleable(),
            Tables\Columns\TextColumn::make('pangkat')->toggleable(),
            Tables\Columns\TextColumn::make('sertifikasi')->toggleable(),
            Tables\Columns\IconColumn::make('aktif')->boolean(),
        ])
            ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()->requiresConfirmation()])
            ->bulkActions([Tables\Actions\DeleteBulkAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOperators::route('/'),
            'create' => Pages\CreateOperator::route('/create'),
            'edit'   => Pages\EditOperator::route('/{record}/edit'),
        ];
    }
}
