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

    // Tambahkan ini untuk menyembunyikan dari sidebar
    protected static bool $shouldRegisterNavigation = false;

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            // Show operator name for the assignment select
            Forms\Components\Select::make('assignment_id')
                ->relationship('assignment', 'id')
                ->getOptionLabelFromRecordUsing(fn($record) => ($record->user->name ?? '—') . ' - ' . ($record->heavyEquipment->nama ?? '—'))
                ->searchable()
                ->required()
                ->preload(),
            Forms\Components\DatePicker::make('tanggal')
                ->label('Tanggal')
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
                ->directory('bbm'),
        ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('tanggal')
                ->label('Tanggal')
                ->date()
                ->sortable(),
            Tables\Columns\TextColumn::make('assignment.workOrder.no_wo')
                ->label('WO')
                ->badge(),
            Tables\Columns\TextColumn::make('assignment.user.name')
                ->label('Operator')
                ->searchable(),
            Tables\Columns\TextColumn::make('liter')
                ->label('Liter')
                ->suffix(' L')
                ->numeric(2)
                ->sortable(),
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

        // Check if the logged-in user has the 'operator' role
        $hasOperatorRole = false;
        try {
            $hasOperatorRole = method_exists($user, 'hasRole') && $user->hasRole('operator');
        } catch (\Throwable $e) {
            $hasOperatorRole = false;
        }

        // If user is an operator, only show their fuel logs
        if ($hasOperatorRole) {
            $query->whereHas('assignment', function ($qa) use ($user) {
                $qa->where('user_id', $user->id);
            });
        }

        return $query;
    }
}
