<?php

namespace App\Filament\Resources;

use App\Filament\Resources\IncidentReportResource\Pages;
use App\Filament\Resources\IncidentReportResource\RelationManagers;
use App\Models\IncidentReport;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class IncidentReportResource extends Resource
{
    protected static ?string $model = IncidentReport::class;
    protected static ?string $navigationIcon = 'heroicon-o-exclamation-triangle';
    protected static ?string $navigationGroup = 'Operasi Lapangan';
    protected static ?string $modelLabel = 'Laporan Insiden';
    protected static ?string $pluralModelLabel = 'Laporan Insiden';

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            Forms\Components\Select::make('work_order_id')->relationship('workOrder', 'no_wo')->searchable()->required()->native(false),
            Forms\Components\Select::make('assignment_id')->relationship('assignment', 'id')->searchable()->required()->native(false),
            Forms\Components\DateTimePicker::make('tanggal')->required(),
            Forms\Components\TextInput::make('kategori')->required(),
            Forms\Components\Select::make('severity')->options(['LOW' => 'LOW', 'MED' => 'MED', 'HIGH' => 'HIGH'])->native(false),
            Forms\Components\Textarea::make('deskripsi')->rows(3),
            Forms\Components\FileUpload::make('foto_bukti')->image()->directory('incidents'),
        ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('tanggal')->dateTime()->sortable(),
            Tables\Columns\TextColumn::make('workOrder.no_wo')->label('WO')->badge(),
            Tables\Columns\TextColumn::make('kategori')->badge(),
            Tables\Columns\TextColumn::make('severity')->badge()->colors([
                'success' => 'LOW',
                'warning' => 'MED',
                'danger' => 'HIGH'
            ]),
        ])
            ->defaultSort('tanggal', 'desc')
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()->requiresConfirmation(),
            ])
            ->bulkActions([Tables\Actions\DeleteBulkAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListIncidentReports::route('/'),
            'create' => Pages\CreateIncidentReport::route('/create'),
            'edit' => Pages\EditIncidentReport::route('/{record}/edit'),
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
