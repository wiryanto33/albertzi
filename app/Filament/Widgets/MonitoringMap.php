<?php

namespace App\Filament\Widgets;

use App\Models\WorkOrder;
use Filament\Widgets\Widget;

class MonitoringMap extends Widget
{
    protected static string $view = 'filament.widgets.monitoring-map';

    protected static ?int $sort = 20;

    protected int|string|array $columnSpan = 'full';

    protected function getViewData(): array
    {
        $items = WorkOrder::query()
            ->with(['project:id,nama,lat,lng'])
            ->where('status', 'AKTIF') // "Berjalan"
            ->get(['id', 'project_id', 'no_wo', 'status'])
            ->map(function ($wo) {
                return [
                    'id' => $wo->id,
                    'wo' => $wo->no_wo,
                    'status' => $wo->status,
                    'project' => [
                        'name' => $wo->project->nama ?? null,
                        'lat' => $wo->project->lat ?? null,
                        'lng' => $wo->project->lng ?? null,
                    ],
                ];
            })
            ->values()
            ->all();

        return [
            'items' => $items,
        ];
    }
}
