<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AssignmentResource\Pages;
use App\Models\Assignment;
use App\Models\WorkOrder;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Set;
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
                ->required()
                ->live()
                ->afterStateUpdated(function (Set $set, $state) {
                    if($state) {
                        $workOrder = WorkOrder::find($state);
                        if($workOrder) {
                            $set('tgl_mulai', $workOrder->tgl_mulai_rencana);
                            $set('tgl_selesai', $workOrder->tgl_selesai_rencana);
                        }
                    } else {
                        $set('tgl_mulai', null);
                        $set('tgl_selesai', null);
                    }
                }),
            Forms\Components\Select::make('heavy_equipment_id')
                ->label('Alat Berat')
                ->relationship('heavyEquipment', 'nama')
                ->preload()
                ->searchable()
                ->required(),
            Forms\Components\Select::make('user_id')
                ->label('Operator')
                ->relationship('user', 'name', function (Builder $query) {
                    // Filter users with 'operator' role
                    $query->whereHas('roles', function ($q) {
                        $q->where('name', 'operator');
                    });
                })
                ->preload()
                ->searchable()
                ->required(),
            Forms\Components\Grid::make(2)->schema([
                Forms\Components\DatePicker::make('tgl_mulai')
                    ->label('Tanggal Mulai')
                    ->native(false),
                Forms\Components\DatePicker::make('tgl_selesai')
                    ->label('Tanggal Selesai')
                    ->native(false),
                // Forms\Components\DatePicker::make('tgl_mulai')
                //     ->label('Tanggal Mulai'),
                // Forms\Components\DatePicker::make('tgl_selesai')
                //     ->label('Tanggal Selesai'),
                Forms\Components\Select::make('status')
                    ->options([
                        'AKTIF' => 'AKTIF',
                        'SELESAI' => 'SELESAI',
                        'DIBATALKAN' => 'DIBATALKAN'
                    ])
                    ->native(false)
                    ->required(),
            ])
        ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('workOrder.no_wo')
                ->label('WO')
                ->badge()
                ->weight('bold')
                ->searchable(),
            Tables\Columns\TextColumn::make('heavyEquipment.nama')
                ->label('Alat')
                ->searchable(),
            Tables\Columns\TextColumn::make('user.name')
                ->label('Operator')
                ->searchable(),
            Tables\Columns\TextColumn::make('status')
                ->badge()
                ->colors([
                    'success' => 'AKTIF',
                    'primary' => 'SELESAI',
                    'danger' => 'DIBATALKAN'
                ]),
            Tables\Columns\TextColumn::make('tgl_mulai')
                ->label('Tgl Mulai')
                ->date(),
            Tables\Columns\TextColumn::make('tgl_selesai')
                ->label('Tgl Selesai')
                ->date(),
            Tables\Columns\TextColumn::make('daily_reports_count')
                ->counts('dailyReports')
                ->label('Laporan'),
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
        return [];
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

        // Check if the logged-in user has the 'operator' role
        $hasOperatorRole = false;
        try {
            $hasOperatorRole = method_exists($user, 'hasRole') && $user->hasRole('operator');
        } catch (\Throwable $e) {
            $hasOperatorRole = false;
        }

        // If user is an operator, only show their assignments
        if ($hasOperatorRole) {
            $query->where('user_id', $user->id);
        }

        return $query;
    }
}
