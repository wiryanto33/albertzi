<?php

namespace App\Filament\Resources;

use App\Filament\Resources\IncidentReportResource\Pages;
use App\Filament\Resources\IncidentReportResource\RelationManagers;
use App\Models\IncidentReport;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
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

    // Tambahkan ini untuk menyembunyikan dari sidebar
    protected static bool $shouldRegisterNavigation = false;

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            Forms\Components\Select::make('work_order_id')
                ->label('Work Order')
                ->relationship('workOrder', 'no_wo')
                ->searchable()
                ->required()
                ->native(false)
                ->live()
                ->afterStateUpdated(fn(Forms\Set $set) => $set('assignment_id', null)),

            Forms\Components\Select::make('assignment_id')
                ->label('Penugasan (Operator - Alat)')
                ->relationship(
                    name: 'assignment',
                    titleAttribute: 'id',
                    modifyQueryUsing: fn(Builder $query, Get $get) => $query->where('work_order_id', $get('work_order_id')),
                )
                ->getOptionLabelFromRecordUsing(fn($record) => ($record->user->name ?? '—') . ' - ' . ($record->heavyEquipment->nama ?? '—'))
                ->searchable()
                ->required()
                ->native(false)
                ->preload(),

            Forms\Components\DateTimePicker::make('tanggal')
                ->label('Tanggal & Waktu')
                ->required()
                ->native(false),

            Forms\Components\TextInput::make('kategori')
                ->label('Kategori Insiden')
                ->placeholder('contoh: Kecelakaan, Kerusakan, Dll')
                ->required(),

            Forms\Components\Select::make('severity')
                ->label('Tingkat Keparahan')
                ->options([
                    'LOW' => 'Rendah (LOW)',
                    'MED' => 'Sedang (MED)',
                    'HIGH' => 'Tinggi (HIGH)'
                ])
                ->native(false)
                ->required(),

            Forms\Components\Textarea::make('deskripsi')
                ->label('Deskripsi Insiden')
                ->rows(4)
                ->columnSpanFull(),

            Forms\Components\FileUpload::make('foto_bukti')
                ->label('Foto Bukti')
                ->image()
                ->directory('incidents')
                ->maxSize(5120)
                ->columnSpanFull(),
        ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('tanggal')
                ->label('Tanggal')
                ->dateTime('d M Y, H:i')
                ->sortable(),

            Tables\Columns\TextColumn::make('workOrder.no_wo')
                ->label('WO')
                ->badge()
                ->searchable(),

            Tables\Columns\TextColumn::make('assignment.user.name')
                ->label('Operator')
                ->searchable(),

            Tables\Columns\TextColumn::make('kategori')
                ->label('Kategori')
                ->badge()
                ->searchable(),

            Tables\Columns\TextColumn::make('severity')
                ->label('Keparahan')
                ->badge()
                ->colors([
                    'success' => 'LOW',
                    'warning' => 'MED',
                    'danger' => 'HIGH'
                ]),
        ])
            ->defaultSort('tanggal', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('severity')
                    ->label('Tingkat Keparahan')
                    ->options([
                        'LOW' => 'Rendah',
                        'MED' => 'Sedang',
                        'HIGH' => 'Tinggi',
                    ]),
            ])
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

        // Check if the logged-in user has the 'operator' role
        $hasOperatorRole = false;
        try {
            $hasOperatorRole = method_exists($user, 'hasRole') && $user->hasRole('operator');
        } catch (\Throwable $e) {
            $hasOperatorRole = false;
        }

        // If user is an operator, only show their incident reports
        if ($hasOperatorRole) {
            $query->whereHas('assignment', function ($qa) use ($user) {
                $qa->where('user_id', $user->id);
            });
        }

        return $query;
    }
}
