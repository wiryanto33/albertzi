<?php

namespace App\Filament\Widgets;

use App\Models\HeavyEquipment;
use Filament\Widgets\ChartWidget;

class EquipmentStatusPieChart extends ChartWidget
{
    protected static ?string $heading = 'Distribusi Status Alat';

    protected static ?int $sort = 11;

    protected int|string|array $columnSpan = 1;

    protected function getData(): array
    {
        $siap = (int) HeavyEquipment::where('status_kesiapan', 'SIAP')->count();
        $tidakSiap = (int) HeavyEquipment::where('status_kesiapan', 'TIDAK_SIAP')->count();
        $perbaikan = (int) HeavyEquipment::where('status_kesiapan', 'PERBAIKAN')->count();

        return [
            'datasets' => [
                [
                    'label' => 'Status Alat',
                    'data' => [$siap, $tidakSiap, $perbaikan],
                    'backgroundColor' => [
                        '#16a34a', // siap - green
                        '#f59e0b', // tidak siap - amber
                        '#ef4444', // perbaikan - red
                    ],
                ],
            ],
            'labels' => ['SIAP', 'TIDAK_SIAP', 'PERBAIKAN'],
        ];
    }

    protected function getType(): string
    {
        return 'pie';
    }
}

