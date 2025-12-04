<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AssignmentResource\Pages;
use App\Filament\Resources\AssignmentResource\RelationManagers;
use App\Filament\Resources\AssignmentResource\RelationManagers\DailyReportsRelationManager;
use App\Filament\Resources\AssignmentResource\RelationManagers\FuelLogsRelationManager;
use App\Models\Assignment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AssignmentResource extends Resource
{
    protected static ?string $model = Assignment::class;
    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationGroup = 'Operasi Lapangan';
    protected static ?string $modelLabel = 'Penugasan';
    protected static ?string $pluralModelLabel = 'Penugasan';

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            Forms\Components\Select::make('work_order_id')
                ->label('SP Kerja')
                ->relationship('workOrder', 'no_wo')
                ->preload()
                ->searchable()
                ->required(),
            Forms\Components\Select::make('heavy_equipment_id')
            ->label('Alat Berat')
            ->relationship('heavyEquipment', 'nama')->preload()->searchable()->required(),
            Forms\Components\Select::make('operator_id')
                ->relationship('operator', 'nama', function (Builder $query) {
                    $query->whereHas('user', function ($q) {
                        // hanya operator yang user-nya memiliki role 'operator'
                        $q->role('operator');
                    });
                })
                ->preload()
                ->searchable()
                ->required(),
            Forms\Components\Grid::make(3)->schema([
                Forms\Components\DatePicker::make('tgl_mulai'),
                Forms\Components\DatePicker::make('tgl_selesai'),
                Forms\Components\Select::make('status')->options([
                    'AKTIF' => 'AKTIF',
                    'SELESAI' => 'SELESAI',
                    'DIBATALKAN' => 'DIBATALKAN'
                ])->native(false)->required(),
            ])
        ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('workOrder.no_wo')->label('WO')->badge()->weight('bold')->searchable(),
            Tables\Columns\TextColumn::make('heavyEquipment.nama')->label('Alat')->searchable(),
            Tables\Columns\TextColumn::make('operator.nama')->label('Operator')->searchable(),
            Tables\Columns\TextColumn::make('status')->badge()->colors([
                'success' => 'AKTIF',
                'primary' => 'SELESAI',
                'danger' => 'DIBATALKAN'
            ]),
            Tables\Columns\TextColumn::make('tgl_mulai')->date(),
            Tables\Columns\TextColumn::make('tgl_selesai')->date(),
            Tables\Columns\TextColumn::make('daily_reports_count')->counts('dailyReports')->label('Laporan'),
        ])
            ->defaultSort('tgl_mulai', 'desc')
            ->filters([])
            ->actions([
                Tables\Actions\Action::make('downloadSuratTugas')
                    ->label('Surat Tugas')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->url(fn($record) => route('assignments.surat-tugas', $record))
                    ->openUrlInNewTab(true),
                Tables\Actions\ViewAction::make()
                    ->url(fn($record) => static::getUrl('view', ['record' => $record]))
                    ->openUrlInNewTab(false),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()->requiresConfirmation(),
            ])
            ->bulkActions([Tables\Actions\DeleteBulkAction::make()]);
    }

    public static function getRelations(): array
    {
        return [
            DailyReportsRelationManager::class,
            FuelLogsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAssignments::route('/'),
            'create' => Pages\CreateAssignment::route('/create'),
            'edit' => Pages\EditAssignment::route('/{record}/edit'),
            'view' => Pages\ViewAssignment::route('/{record}'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        $user = auth()->user();
        if (! $user) {
            // Hide everything for unauthenticated context
            return $query->whereRaw('1 = 0');
        }

        // When the logged-in user is an operator (role or has operator record),
        // restrict to their assignments
        $hasOperatorRole = false;
        try {
            $hasOperatorRole = method_exists($user, 'hasRole') ? $user->hasRole('operator') : false;
        } catch (\Throwable $e) {
            $hasOperatorRole = false;
        }

        $operatorId = \App\Models\Operator::where('user_id', $user->id)->value('id');

        if ($hasOperatorRole || $operatorId) {
            if ($operatorId) {
                $query->where('operator_id', $operatorId);
            } else {
                // If role says operator but no linked profile, hide entries
                $query->whereRaw('1 = 0');
            }
        }

        return $query;
    }
}
