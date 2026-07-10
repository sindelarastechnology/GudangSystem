<?php

namespace App\Filament\Widgets;

use App\Models\MaterialStock;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class TopItemsByValueWidget extends ChartWidget
{
    protected static ?string $heading = 'Top 10 Item Berdasarkan Nilai Aset';

    protected static ?string $description = 'Item dengan nilai aset tertinggi di gudang';

    protected static ?int $sort = 5;

    protected int | string | array $columnSpan = 'full';

    protected static ?string $maxHeight = '350px';

    public ?string $filter = 'all';

    protected function getData(): array
    {
        $query = MaterialStock::query()
            ->select(
                'material_stocks.raw_material_id',
                DB::raw('SUM(material_stocks.current_stock * material_stocks.current_avg_cost) as total_value'),
                DB::raw('SUM(material_stocks.current_stock) as total_qty')
            )
            ->join('raw_materials', 'material_stocks.raw_material_id', '=', 'raw_materials.id')
            ->where('material_stocks.current_stock', '>', 0)
            ->when($this->filter !== 'all', function ($query) {
                return $query->where('material_stocks.warehouse_id', $this->filter);
            })
            ->groupBy('material_stocks.raw_material_id')
            ->orderByDesc('total_value')
            ->limit(10)
            ->with('rawMaterial.unit');

        $topItems = $query->get();

        $labels = [];
        $values = [];
        $backgroundColors = [];
        $borderColors = [];

        // Color palette untuk chart
        $colors = [
            ['bg' => 'rgba(239, 68, 68, 0.7)', 'border' => 'rgb(239, 68, 68)'],
            ['bg' => 'rgba(249, 115, 22, 0.7)', 'border' => 'rgb(249, 115, 22)'],
            ['bg' => 'rgba(245, 158, 11, 0.7)', 'border' => 'rgb(245, 158, 11)'],
            ['bg' => 'rgba(234, 179, 8, 0.7)', 'border' => 'rgb(234, 179, 8)'],
            ['bg' => 'rgba(132, 204, 22, 0.7)', 'border' => 'rgb(132, 204, 22)'],
            ['bg' => 'rgba(34, 197, 94, 0.7)', 'border' => 'rgb(34, 197, 94)'],
            ['bg' => 'rgba(16, 185, 129, 0.7)', 'border' => 'rgb(16, 185, 129)'],
            ['bg' => 'rgba(20, 184, 166, 0.7)', 'border' => 'rgb(20, 184, 166)'],
            ['bg' => 'rgba(6, 182, 212, 0.7)', 'border' => 'rgb(6, 182, 212)'],
            ['bg' => 'rgba(59, 130, 246, 0.7)', 'border' => 'rgb(59, 130, 246)'],
        ];

        foreach ($topItems as $index => $item) {
            $rawMaterial = $item->rawMaterial;
            $label = strlen($rawMaterial->name) > 25 
                ? substr($rawMaterial->name, 0, 25) . '...' 
                : $rawMaterial->name;
            
            $labels[] = $label;
            $values[] = round($item->total_value / 1000, 2); // Dalam ribuan untuk readability
            $backgroundColors[] = $colors[$index]['bg'];
            $borderColors[] = $colors[$index]['border'];
        }

        return [
            'datasets' => [
                [
                    'label' => 'Nilai Aset (Ribu Rupiah)',
                    'data' => $values,
                    'backgroundColor' => $backgroundColors,
                    'borderColor' => $borderColors,
                    'borderWidth' => 2,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'indexAxis' => 'y',
            'plugins' => [
                'legend' => [
                    'display' => false,
                ],
                'tooltip' => [
                    'enabled' => true,
                    'callbacks' => [
                        'label' => 'function(context) { return "Rp " + (context.parsed.x * 1000).toLocaleString("id-ID"); }',
                    ],
                ],
            ],
            'scales' => [
                'x' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'callback' => 'function(value) { return "Rp " + (value * 1000).toLocaleString("id-ID"); }',
                    ],
                ],
                'y' => [
                    'ticks' => [
                        'autoSkip' => false,
                    ],
                ],
            ],
            'maintainAspectRatio' => false,
        ];
    }

    protected function getFilters(): ?array
    {
        $warehouses = \App\Models\Warehouse::where('is_active', true)
            ->pluck('name', 'id')
            ->toArray();

        return array_merge(['all' => 'Semua Gudang'], $warehouses);
    }
}
