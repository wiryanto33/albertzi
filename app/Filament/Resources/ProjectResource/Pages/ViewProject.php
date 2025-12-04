<?php

namespace App\Filament\Resources\ProjectResource\Pages;

use App\Filament\Resources\ProjectResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Pages\Actions;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Placeholder;

class ViewProject extends ViewRecord
{
    protected static string $resource = ProjectResource::class;

    protected function getActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Add any computed placeholders if needed
        return $data;
    }

    protected function getFormSchema(): array
    {
        return [
            Card::make()->schema([
                Grid::make(2)->schema([
                    Placeholder::make('nama')->label('Nama Proyek')->content(fn(?\App\Models\Project $record) => $record?->nama ?? '—'),
                    Placeholder::make('client')->label('Client')->content(fn(?\App\Models\Project $record) => $record?->client?->nama ?? '—'),
                    Placeholder::make('lokasi')->label('Lokasi')->content(fn(?\App\Models\Project $record) => $record?->lokasi_nama ?? '—'),
                    Placeholder::make('status')->label('Status')->content(fn(?\App\Models\Project $record) => $record?->status ?? '—'),
                    Placeholder::make('tanggal_mulai')->label('Tanggal Mulai')->content(fn(?\App\Models\Project $record) => optional($record?->tanggal_mulai)->toDateString() ?? '—'),
                    Placeholder::make('tanggal_selesai')->label('Tanggal Selesai')->content(fn(?\App\Models\Project $record) => optional($record?->tanggal_selesai)->toDateString() ?? '—'),
                ]),
            ]),
        ];
    }
}
