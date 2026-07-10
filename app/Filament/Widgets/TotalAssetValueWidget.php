<?php

namespace App\Filament\Widgets;

use App\Models\MaterialStock;
use App\Models\StockLedger;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class TotalAssetValueWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getColumns(): int
    {
        return 4;
    }

    protected function getStats(): array
    {
        // Total nilai aset saat ini
        $totalValue = MaterialStock::sum(DB::raw('current_stock * current_avg_cost'));
        $totalItems = MaterialStock::where('current_stock', '>', 0)->count();
        $totalItemsAll = MaterialStock::count();

        // Hitung nilai aset bulan lalu untuk trend
        $lastMonthStart = now()->subMonth()->startOfMonth();
        $lastMonthEnd = now()->subMonth()->endOfMonth();
        
        $lastMonthValue = StockLedger::whereDate('transaction_date', '<=', $lastMonthEnd)
            ->select('raw_material_id', 'warehouse_id')
            ->selectRaw('MAX(running_asset_value) as asset_value')
            ->groupBy('raw_material_id', 'warehouse_id')
            ->get()
            ->sum('asset_value');

        // Hitung growth
        $growth = $lastMonthValue > 0 
            ? (($totalValue - $lastMonthValue) / $lastMonthValue) * 100 
            : 0;

        // Total transaksi bulan ini
        $transactionsThisMonth = StockLedger::whereMonth('transaction_date', now()->month)
            ->whereYear('transaction_date', now()->year)
            ->count();

        // Items dengan stok kritis
        $criticalItems = MaterialStock::whereRaw('current_stock <= min_stock')
            ->where('min_stock', '>', 0)
            ->count();

        $stats = [];

        // Stat 1: Total Nilai Aset
        $stats[] = Stat::make('Total Nilai Aset', 'Rp ' . number_format($totalValue, 0, ',', '.'))
            ->description($growth >= 0 
                ? 'Naik ' . number_format(abs($growth), 1) . '% dari bulan lalu'
                : 'Turun ' . number_format(abs($growth), 1) . '% dari bulan lalu'
            )
            ->descriptionIcon($growth >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
            ->color($growth >= 0 ? 'success' : 'danger')
            ->chart($this->getAssetTrendChart());

        // Stat 2: Total Item
        $stats[] = Stat::make('Total Item', number_format($totalItems, 0))
            ->description($totalItemsAll . ' item terdaftar')
            ->descriptionIcon('heroicon-m-cube')
            ->color('info');

        // Stat 3: Transaksi Bulan Ini
        $stats[] = Stat::make('Transaksi Bulan Ini', number_format($transactionsThisMonth, 0))
            ->description('Semua jenis transaksi')
            ->descriptionIcon('heroicon-m-arrow-path')
            ->color('warning');

        // Stat 4: Stok Kritis
        $criticalColor = $criticalItems > 0 ? 'danger' : 'success';
        $criticalDescription = $criticalItems > 0 
            ? 'Perlu perhatian segera!'
            : 'Semua stok aman';
        
        $stats[] = Stat::make('Item Kritis', number_format($criticalItems, 0))
            ->description($criticalDescription)
            ->descriptionIcon($criticalItems > 0 ? 'heroicon-m-exclamation-triangle' : 'heroicon-m-check-circle')
            ->color($criticalColor);

        // Stats per gudang
        $warehouseValues = MaterialStock::where('current_stock', '>', 0)
            ->select('warehouse_id')
            ->selectRaw('SUM(current_stock * current_avg_cost) as total_value')
            ->selectRaw('COUNT(*) as item_count')
            ->selectRaw('SUM(CASE WHEN current_stock <= min_stock AND min_stock > 0 THEN 1 ELSE 0 END) as critical_count')
            ->with('warehouse')
            ->groupBy('warehouse_id')
            ->get();

        foreach ($warehouseValues as $wv) {
            $percentage = $totalValue > 0 ? ($wv->total_value / $totalValue) * 100 : 0;
            
            $stats[] = Stat::make(
                $wv->warehouse->name, 
                'Rp ' . number_format($wv->total_value, 0, ',', '.')
            )
                ->description(
                    number_format($percentage, 1) . '% dari total • ' . 
                    $wv->item_count . ' item' .
                    ($wv->critical_count > 0 ? ' • ' . $wv->critical_count . ' kritis' : '')
                )
                ->descriptionIcon('heroicon-m-building-storefront')
                ->color($wv->critical_count > 0 ? 'warning' : 'success');
        }

        return $stats;
    }

    /**
     * Generate mini chart data untuk trend 6 bulan terakhir
     */
    private function getAssetTrendChart(): array
    {
        $data = [];
        
        for ($i = 5; $i >= 0; $i--) {
            $monthEnd = now()->subMonths($i)->endOfMonth();
            
            $value = StockLedger::whereDate('transaction_date', '<=', $monthEnd)
                ->select('raw_material_id', 'warehouse_id')
                ->selectRaw('MAX(running_asset_value) as asset_value')
                ->groupBy('raw_material_id', 'warehouse_id')
                ->get()
                ->sum('asset_value');
            
            $data[] = $value / 1000000; // Dalam jutaan untuk chart
        }

        return $data;
    }
}
