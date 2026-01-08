<?php

namespace App\Filament\Resources\DailyReportResource\Pages;

use App\Filament\Resources\DailyReportResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateDailyReport extends CreateRecord
{
    protected static string $resource = DailyReportResource::class;
    protected static bool $canCreateAnother = false;

    //customize redirect after create
    public function getRedirectUrl(): string
    {
        // Redirect ke halaman edit setelah create agar bisa langsung isi fuel log & incident
        return $this->getResource()::getUrl('edit', ['record' => $this->getRecord()]);
    }
}
