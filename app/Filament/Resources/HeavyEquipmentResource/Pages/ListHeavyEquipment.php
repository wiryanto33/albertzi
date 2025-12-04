<?php

namespace App\Filament\Resources\HeavyEquipmentResource\Pages;

use App\Filament\Resources\HeavyEquipmentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListHeavyEquipment extends ListRecords
{
    protected static string $resource = HeavyEquipmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
