<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-home';

    public function getColumns(): int|string|array
    {
        // 1 col on small, 2 cols on md+
        return [
            'sm' => 1,
            'md' => 2,
            'xl' => 2,
        ];
    }
}
