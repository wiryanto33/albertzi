<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DailyReportResource\Pages;
use App\Filament\Resources\DailyReportResource\RelationManagers;
use App\Models\DailyReport;
use App\Models\Assignment;
use App\Models\WorkOrder;
use App\Support\FilamentHelpers;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class DailyReportResource extends Resource
{
    protected static ?string $model = DailyReport::class;
    protected static ?string $navigationIcon = 'heroicon-o-newspaper';
    protected static ?string $navigationGroup = 'Operasi Lapangan';
    protected static ?string $modelLabel = 'Laporan Harian';
    protected static ?string $pluralModelLabel = 'Laporan Harian';



    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Informasi Pekerjaan')->schema([
                Forms\Components\Grid::make(2)->schema([
                    Forms\Components\Select::make('work_order_id')
                        ->relationship('workOrder', 'no_wo')
                        ->searchable()
                        ->required()
                        ->preload()
                        ->live()
                        ->afterStateHydrated(function (Set $set, $state) {
                            $lat = null;
                            $lng = null;
                            if ($state) {
                                $wo = WorkOrder::with('project')->find($state);
                                if ($wo && $wo->project) {
                                    $lat = $wo->project->lat;
                                    $lng = $wo->project->lng;
                                }
                            }
                            $set('project_lat', $lat);
                            $set('project_lng', $lng);
                        })
                        ->afterStateUpdated(function (Set $set, $state) {
                            // Reset penugasan saat WO berubah
                            $set('assignment_id', null);

                            // Ambil koordinat proyek untuk peta
                            $lat = null;
                            $lng = null;
                            if ($state) {
                                $wo = WorkOrder::with('project')->find($state);
                                if ($wo && $wo->project) {
                                    $lat = $wo->project->lat;
                                    $lng = $wo->project->lng;
                                }
                            }
                            $set('project_lat', $lat);
                            $set('project_lng', $lng);

                            // Auto-select assignment untuk user yang login
                            $userId = auth()->id();
                            if ($state && $userId) {
                                $assignment = Assignment::where('work_order_id', $state)
                                    ->where('user_id', $userId)
                                    ->first();

                                if ($assignment) {
                                    $set('assignment_id', $assignment->id);
                                    $set('operator_nama', $assignment->user->name ?? null);
                                }
                            }
                        }),

                    Forms\Components\Select::make('assignment_id')
                        ->label('Penugasan (Operator - Alat)')
                        ->relationship(
                            name: 'assignment',
                            titleAttribute: 'id',
                            modifyQueryUsing: fn(Builder $query, Get $get) => $query->where('work_order_id', $get('work_order_id')),
                        )
                        ->getOptionLabelFromRecordUsing(fn($record) => ($record->user->name ?? '—') . ' - ' . ($record->heavyEquipment->nama ?? '—'))
                        ->searchable()
                        ->preload()
                        ->afterStateHydrated(function (Set $set, $state) {
                            $name = null;
                            if ($state) {
                                $as = Assignment::with('user')->find($state);
                                if ($as && $as->user) {
                                    $name = $as->user->name;
                                }
                            }
                            $set('operator_nama', $name);
                        })
                        ->afterStateUpdated(function (Set $set, $state) {
                            $name = null;
                            if ($state) {
                                $as = Assignment::with('user')->find($state);
                                if ($as && $as->user) {
                                    $name = $as->user->name;
                                }
                            }
                            $set('operator_nama', $name);
                        })
                        ->required(),
                ]),
            ])->collapsible(),

            Forms\Components\Section::make('Waktu & Progress')->schema([
                Forms\Components\Grid::make(4)->schema([
                    Forms\Components\DatePicker::make('tanggal')->required()->columnSpan(1),
                    Forms\Components\TextInput::make('progress_persen')->numeric()->suffix('%')->minValue(0)->maxValue(100)->required()->columnSpan(1),
                    Forms\Components\TextInput::make('jam_kerja_operator')->numeric()->step('any')->columnSpan(1)
                        ->dehydrateStateUsing(fn($state) => blank($state) ? null : round((float) str_replace(',', '.', (string) $state), 2)),
                    Forms\Components\TextInput::make('jam_jalan_alat')->numeric()->step('any')->columnSpan(1)
                        ->dehydrateStateUsing(fn($state) => blank($state) ? null : round((float) str_replace(',', '.', (string) $state), 2)),
                ]),
            ])->collapsible(),

            Forms\Components\Section::make('Lokasi & Cuaca')->schema([
                Forms\Components\Grid::make(3)->schema([
                    Forms\Components\TextInput::make('lat')->live()
                        ->label('Lat')
                        ->numeric()->step('any')
                        ->dehydrateStateUsing(function ($state) {
                            if (blank($state)) return null;
                            $s = trim((string) $state);
                            $s = str_replace("\u{00A0}", '', $s);
                            $s = str_replace(' ', '', $s);
                            $s = str_replace(',', '.', $s);
                            if (substr_count($s, '.') > 1) {
                                $s = preg_replace('/\.(?=.*\.)/', '', $s);
                            }
                            return is_numeric($s) ? (float) $s : null;
                        })
                        ->suffixAction(
                            Forms\Components\Actions\Action::make('geo')
                                ->label('Lokasi')
                                ->tooltip('Gunakan lokasi saya')
                                ->icon('heroicon-m-map-pin')
                                ->alpineClickHandler('window.navigator.geolocation.getCurrentPosition((pos)=>{ const la = pos.coords.latitude.toFixed(6); const lo = pos.coords.longitude.toFixed(6); $wire.set("data.lat", la, true); $wire.set("data.lng", lo, true); }, (e)=>{ console.warn(e?.message ?? "Geolocation error"); });')
                        ),
                    Forms\Components\TextInput::make('lng')->live()->label('Lng')->numeric()->step('any')
                        ->dehydrateStateUsing(function ($state) {
                            if (blank($state)) return null;
                            $s = trim((string) $state);
                            $s = str_replace("\u{00A0}", '', $s);
                            $s = str_replace(' ', '', $s);
                            $s = str_replace(',', '.', $s);
                            if (substr_count($s, '.') > 1) {
                                $s = preg_replace('/\.(?=.*\.)/', '', $s);
                            }
                            return is_numeric($s) ? (float) $s : null;
                        }),
                    Forms\Components\TextInput::make('cuaca')->label('Cuaca'),
                ]),

                // Field helper (hanya untuk peta; tidak disimpan)
                Forms\Components\TextInput::make('project_lat')->dehydrated(false)->hidden(),
                Forms\Components\TextInput::make('project_lng')->dehydrated(false)->hidden(),
                Forms\Components\TextInput::make('operator_nama')->dehydrated(false)->hidden(),

                // Peta gabungan 2 marker (operator & lokasi kerja)
                Forms\Components\ViewField::make('map')
                    ->view('filament.forms.map-two-markers')
                    ->dehydrated(false)
                    ->reactive()
                    ->columnSpanFull()
                    ->hint('Klik ikon pin untuk auto-lokasi. Seret marker operator untuk mengubah koordinat.'),
            ])->collapsible()->columns(1),

            Forms\Components\Section::make('Uraian Pekerjaan')->schema([
                Forms\Components\Textarea::make('uraian')->rows(3),
            ])->collapsible(),

            Forms\Components\Section::make('Dokumentasi Foto')->schema([
                Forms\Components\Repeater::make('photos')
                    ->relationship('photos')
                    ->schema([
                        Forms\Components\FileUpload::make('path')
                            ->image()
                            ->directory('daily-reports')
                            ->required(),
                        Forms\Components\TextInput::make('caption')->label('Keterangan'),
                    ])->columns(2)->minItems(0)
                    ->addActionLabel('Tambah Foto')
                    ->collapsed(false),
            ])->collapsible(),

            Forms\Components\Section::make('Metadata')->schema([
                Forms\Components\Select::make('created_by')
                    ->relationship('creator', 'name')
                    ->searchable()
                    ->preload()
                    ->label('Dibuat Oleh'),
            ])->collapsible(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('tanggal')->date()->sortable(),
            Tables\Columns\TextColumn::make('workOrder.no_wo')->badge()->label('WO'),
            Tables\Columns\TextColumn::make('assignment.user.name')->label('Operator'),
            Tables\Columns\TextColumn::make('progress_persen')->label('Progres')
                ->formatStateUsing(fn($state) => $state . '%')
                ->color(fn($state) => $state >= 80 ? 'success' : ($state >= 50 ? 'warning' : 'danger'))
                ->sortable(),
            Tables\Columns\TextColumn::make('jam_jalan_alat')->label('Jam Alat'),
        ])
            ->defaultSort('tanggal', 'desc')
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()->requiresConfirmation(),
            ])
            ->bulkActions([Tables\Actions\DeleteBulkAction::make()]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\FuelLogsRelationManager::class,
            RelationManagers\IncidentReportsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDailyReports::route('/'),
            'create' => Pages\CreateDailyReport::route('/create'),
            'view' => Pages\ViewDailyReport::route('/{record}'),
            'edit' => Pages\EditDailyReport::route('/{record}/edit'),
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

        if ($hasOperatorRole) {
            // Show only reports for the operator's assignments OR created by the user
            $query->where(function ($q) use ($user) {
                $q->whereHas('assignment', function ($qa) use ($user) {
                    $qa->where('user_id', $user->id);
                })
                    ->orWhere('created_by', $user->id);
            });
        }

        return $query;
    }
}
