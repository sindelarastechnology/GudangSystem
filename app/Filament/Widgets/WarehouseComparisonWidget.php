<?php

namespace App\Filament\Widgets;

use App\Models\MaterialStock;
use App\Models\Warehouse;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class WarehouseComparisonWidget extends BaseWidget
{
    protected static ?int $sort = 2;

    protected int | string | array $columnSpan = 'full';

    protected function getColumns(): int
    {
        return 2;
    }

    protected function getStats(): array
    {
        $warehouses = Warehouse::where('is_active', true)
            ->withCount([
                'materialStocks as total_items' => function ($query) {
                    $query->where('current_stock', '>', 0);
                },
                'materialStocks as critical_items' => function ($query) {
                    $query->whereRaw('current_stock <= min_stock')
                        ->where('min_stock', '>', 0);
                },
            ])
            ->get();

        $stats = [];

        // Hitung total perusahaan sekali di luar loop
        $companyTotal = MaterialStock::sum(DB::raw('current_stock * current_avg_cost'));

        foreach ($warehouses as $warehouse) {
            // Hitung total nilai aset gudang ini
            $totalValue = MaterialStock::where('warehouse_id', $warehouse->id)
                ->where('current_stock', '>', 0)
                ->sum(DB::raw('current_stock * current_avg_cost'));
            $percentage = $companyTotal > 0 ? ($totalValue / $companyTotal) * 100 : 0;

            // Hitung inventory turnover (simplified: total out / avg stock value)
            $monthStart = now()->startOfMonth();
            $totalOut = DB::table('stock_out_details')
                ->join('stock_out_transactions', 'stock_out_details.stock_out_transaction_id', '=', 'stock_out_transactions.id')
                ->where('stock_out_transactions.warehouse_id', $warehouse->id)
                ->whereDate('stock_out_transactions.transaction_date', '>=', $monthStart)
                ->sum('stock_out_details.subtotal_hpp');

            $turnoverRate = $totalValue > 0 ? ($totalOut / $totalValue) : 0;

            // Status gudang
            $statusColor = 'success';
            $statusIcon = 'heroicon-m-check-circle';
            $statusText = 'Operasional Normal';

            if ($warehouse->is_locked) {
                $statusColor = 'warning';
                $statusIcon = 'heroicon-m-lock-closed';
                $statusText = 'Sedang Opname';
            } elseif ($warehouse->critical_items > 0) {
                $statusColor = 'danger';
                $statusIcon = 'heroicon-m-exclamation-triangle';
                $statusText = $warehouse->critical_items . ' item kritis';
            }

            $stats[] = Stat::make(
                $warehouse->name,
                'Rp ' . number_format($totalValue, 0, ',', '.')
            )
                ->description(
                    number_format($percentage, 1) . '% dari total • ' .
                    $warehouse->total_items . ' item aktif • ' .
                    'Turnover: ' . number_format($turnoverRate * 100, 1) . '%'
                )
                ->descriptionIcon('heroicon-m-building-storefront')
                ->color($statusColor)
                ->extraAttributes([
                    'class' => 'cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-800 transition',
                ])
                ->chart($this->getWarehouseTrendChart($warehouse->id));
        }

        return $stats;
    }

    /**
     * Generate mini chart untuk trend nilai aset gudang (3 bulan terakhir)
     */
    private function getWarehouseTrendChart(int $warehouseId): array
    {
        $data = [];
        
        for ($i = 2; $i >= 0; $i--) {
            $monthEnd = now()->subMonths($i)->endOfMonth();
            
            $value = DB::table('stock_ledgers')
                ->where('warehouse_id', $warehouseId)
                ->whereDate('transaction_date', '<=', $monthEnd)
                ->select('raw_material_id')
                ->selectRaw('MAX(running_asset_value) as asset_value')
                ->groupBy('raw_material_id')
                ->get()
                ->sum('asset_value');
            
            $data[] = $value / 1000000; // Dalam jutaan
        }

        return $data;
    }
}
