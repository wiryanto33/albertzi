<?php

namespace App\Filament\Widgets;

use App\Models\HeavyEquipment;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class HeavyEquipmentStats extends BaseWidget
{
    protected ?string $heading = 'Statistik Alat Berat';

    protected static ?int $sort = 10;

    // Keep full width even when dashboard has 2 columns
    protected int|string|array $columnSpan = 'full';

    protected function getStats(): array
    {
        $total = HeavyEquipment::count();
        $siap = HeavyEquipment::where('status_kesiapan', 'SIAP')->count();
        $tidakSiap = HeavyEquipment::where('status_kesiapan', 'TIDAK_SIAP')->count();
        $perbaikan = HeavyEquipment::where('status_kesiapan', 'PERBAIKAN')->count();

        return [
            Stat::make('Total Alat', $total)
                ->icon('heroicon-o-cube')
                ->color('primary')
                ->chart([
                    'Siap' => $siap,
                    'Tidak' => $tidakSiap,
                    'Perbaikan' => $perbaikan,
                ])
                ->chartColor('gray'),

            Stat::make('Siap Operasi', $siap)
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->description(match ($total) {
                    0 => '—',
                    default => round(($siap / max($total, 1)) * 100) . '% dari total',
                })
                ->chart([
                    'Siap' => $siap,
                    'Tidak' => $tidakSiap,
                    'Perbaikan' => $perbaikan,
                ])
                ->chartColor('success'),

            Stat::make('Tidak Siap', $tidakSiap)
                ->icon('heroicon-o-x-circle')
                ->color('warning')
                ->chart([
                    'Siap' => $siap,
                    'Tidak' => $tidakSiap,
                    'Perbaikan' => $perbaikan,
                ])
                ->chartColor('warning'),

            Stat::make('Dalam Perbaikan', $perbaikan)
                ->icon('heroicon-o-wrench-screwdriver')
                ->color('danger')
                ->chart([
                    'Siap' => $siap,
                    'Tidak' => $tidakSiap,
                    'Perbaikan' => $perbaikan,
                ])
                ->chartColor('danger'),
        ];
    }
}
