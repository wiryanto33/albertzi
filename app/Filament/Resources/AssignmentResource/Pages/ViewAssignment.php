<?php

namespace App\Filament\Resources\AssignmentResource\Pages;

use App\Filament\Resources\AssignmentResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewAssignment extends ViewRecord
{
    protected static string $resource = AssignmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('downloadSuratTugas')
                ->label('Download Surat Tugas')
                ->icon('heroicon-o-arrow-down-tray')
                ->url(fn () => route('assignments.surat-tugas', $this->getRecord()))
                ->openUrlInNewTab(true),
            Actions\EditAction::make(),
            Actions\DeleteAction::make()->requiresConfirmation(),
        ];
    }
}
