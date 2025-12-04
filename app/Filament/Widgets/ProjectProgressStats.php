<?php

namespace App\Filament\Widgets;

use App\Models\Project;
use App\Models\WorkOrder;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ProjectProgressStats extends BaseWidget
{
    protected ?string $heading = 'Progres Proyek';

    protected static ?int $sort = 10;

    // Keep full width even when dashboard has 2 columns
    protected int|string|array $columnSpan = 'full';

    protected function getStats(): array
    {
        $total = Project::count();
        $berjalan = Project::where('status', 'BERJALAN')->count();
        $selesai = Project::where('status', 'SELESAI')->count();

        $progresPersen = $total > 0 ? (int) round(($selesai / $total) * 100) : 0;

        // Tambahan konteks menggunakan data WO sebagai indikator pendukung
        $woTotal = WorkOrder::count();
        $woSelesai = WorkOrder::where('status', 'SELESAI')->count();
        $woProgress = $woTotal > 0 ? (int) round(($woSelesai / $woTotal) * 100) : 0;

        return [
            Stat::make('Total Proyek', $total)
                ->icon('heroicon-o-briefcase')
                ->color('primary')
                ->chart([
                    'Berjalan' => $berjalan,
                    'Selesai' => $selesai,
                ])
                ->chartColor('gray'),

            Stat::make('Berjalan', $berjalan)
                ->icon('heroicon-o-play-circle')
                ->color('success')
                ->chart([
                    'Berjalan' => $berjalan,
                    'Selesai' => $selesai,
                ])
                ->chartColor('success'),

            Stat::make('Selesai', $selesai)
                ->icon('heroicon-o-check-badge')
                ->color('gray')
                ->chart([
                    'Berjalan' => $berjalan,
                    'Selesai' => $selesai,
                ])
                ->chartColor('primary'),

            Stat::make('Progres (Proyek)', $progresPersen . '%')
                ->icon('heroicon-o-chart-bar')
                ->color('info')
                ->description('WO selesai: ' . $woProgress . '%')
                ->chart([
                    '0%' => 0,
                    'Proyek' => $progresPersen,
                    'WO' => $woProgress,
                ])
                ->chartColor('info'),
        ];
    }
}
