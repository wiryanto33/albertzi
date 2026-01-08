<?php

namespace App\Filament\Resources\DailyReportResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class IncidentReportsRelationManager extends RelationManager
{
    protected static string $relationship = 'incidentReports';
    protected static ?string $title = 'Laporan Insiden';
    protected static ?string $recordTitleAttribute = 'kategori';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\DateTimePicker::make('tanggal')
                ->label('Tanggal & Waktu')
                ->default(now())
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
                ->required()
                ->default('LOW'),

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

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('kategori')
            ->columns([
                Tables\Columns\TextColumn::make('tanggal')
                    ->label('Tanggal')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),

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
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        // Auto-assign work_order_id dan assignment_id dari daily report
                        $data['work_order_id'] = $this->getOwnerRecord()->work_order_id;
                        $data['assignment_id'] = $this->getOwnerRecord()->assignment_id;
                        return $data;
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
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
