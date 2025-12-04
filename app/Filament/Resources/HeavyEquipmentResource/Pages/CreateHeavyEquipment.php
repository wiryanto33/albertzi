<?php

namespace App\Filament\Resources\HeavyEquipmentResource\Pages;

use App\Filament\Resources\HeavyEquipmentResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateHeavyEquipment extends CreateRecord
{
    protected static string $resource = HeavyEquipmentResource::class;
    protected static bool $canCreateAnother = false;

    //customize redirect after create
    public function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
