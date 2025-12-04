<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FuelLogResource\Pages;
use App\Filament\Resources\FuelLogResource\RelationManagers;
use App\Models\FuelLog;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class FuelLogResource extends Resource
{
    protected static ?string $model = FuelLog::class;
    protected static ?string $navigationIcon = 'heroicon-o-fire';
    protected static ?string $navigationGroup = 'Operasi Lapangan';
    protected static ?string $modelLabel = 'Log BBM';
    protected static ?string $pluralModelLabel = 'Log BBM';

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            // show operator name for the assignment select
            Forms\Components\Select::make('assignment_id')
                ->relationship('assignment', 'id')
                ->getOptionLabelFromRecordUsing(fn($record) => ($record->operator->nama ?? '—') . ' - ' . ($record->heavyEquipment->nama ?? '—'))
                ->searchable()
                ->required()
                ->preload(),
            Forms\Components\DatePicker::make('tanggal')->required(),
            Forms\Components\TextInput::make('liter')->numeric()->step('0.01')->required(),
            Forms\Components\TextInput::make('odometer_jam_awal')->numeric(),
            Forms\Components\TextInput::make('odometer_jam_akhir')->numeric(),
            Forms\Components\FileUpload::make('bukti_foto')->image()->directory('bbm'),
        ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('tanggal')->date()->sortable(),
            Tables\Columns\TextColumn::make('assignment.workOrder.no_wo')->label('WO')->badge(),
            Tables\Columns\TextColumn::make('liter')->suffix(' L')->numeric(2)->sortable(),
        ])
            ->defaultSort('tanggal', 'desc')
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()->requiresConfirmation(),
            ])
            ->bulkActions([Tables\Actions\DeleteBulkAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFuelLogs::route('/'),
            'create' => Pages\CreateFuelLog::route('/create'),
            'edit' => Pages\EditFuelLog::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        $user = auth()->user();
        if (! $user) {
            return $query->whereRaw('1 = 0');
        }

        $hasOperatorRole = false;
        try {
            $hasOperatorRole = method_exists($user, 'hasRole') ? $user->hasRole('operator') : false;
        } catch (\Throwable $e) {
            $hasOperatorRole = false;
        }

        $operatorId = \App\Models\Operator::where('user_id', $user->id)->value('id');

        if ($hasOperatorRole || $operatorId) {
            $query->whereHas('assignment', function ($qa) use ($operatorId) {
                $qa->where('operator_id', $operatorId);
            });
        }

        return $query;
    }
}
