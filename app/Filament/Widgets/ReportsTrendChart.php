<?php

namespace App\Filament\Widgets;

use App\Models\DailyReport;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class ReportsTrendChart extends ChartWidget
{
    protected static ?string $heading = 'Tren Laporan Harian (7 Hari)';

    protected static ?int $sort = 12;

    protected int|string|array $columnSpan = 1;

    protected function getData(): array
    {
        $end = Carbon::today();
        $start = (clone $end)->subDays(6);

        $labels = [];
        $cursor = (clone $start);
        while ($cursor->lte($end)) {
            $labels[] = $cursor->format('d M');
            $cursor->addDay();
        }

        $raw = DailyReport::query()
            ->whereBetween('tanggal', [$start->toDateString(), $end->toDateString()])
            ->selectRaw('tanggal, COUNT(*) as total')
            ->groupBy('tanggal')
            ->pluck('total', 'tanggal');

        $dataMap = collect(range(0, 6))->mapWithKeys(function ($i) use ($start) {
            $date = (clone $start)->addDays($i)->toDateString();
            return [$date => 0];
        })->toArray();

        foreach ($raw as $date => $count) {
            if (isset($dataMap[$date])) {
                $dataMap[$date] = (int) $count;
            }
        }

        return [
            'datasets' => [
                [
                    'label' => 'Jumlah Laporan',
                    'data' => array_values($dataMap),
                    'borderColor' => '#3b82f6',
                    'backgroundColor' => 'rgba(59,130,246,0.45)',
                    'tension' => 0.35,
                    'fill' => true,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
