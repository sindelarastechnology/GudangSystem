<?php

namespace App\Filament\Widgets;

use App\Models\MaterialStock;
use App\Models\RawMaterial;
use App\Models\StockInTransaction;
use App\Models\StockLedger;
use App\Models\StockOpname;
use App\Models\StockOutTransaction;
use App\Models\StockTransfer;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class QuickStatsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 0;

    protected function getColumns(): int
    {
        return 4;
    }

    protected function getStats(): array
    {
        // Transaksi hari ini
        $today = now()->startOfDay();
        $stockInToday = StockInTransaction::whereDate('transaction_date', $today)->count();
        $stockOutToday = StockOutTransaction::whereDate('transaction_date', $today)->count();
        $transfersToday = StockTransfer::whereDate('transfer_date', $today)->count();
        $totalToday = $stockInToday + $stockOutToday + $transfersToday;

        // Transaksi minggu ini vs minggu lalu
        $weekStart = now()->startOfWeek();
        $lastWeekStart = now()->subWeek()->startOfWeek();
        $lastWeekEnd = now()->subWeek()->endOfWeek();

        $thisWeekCount = StockLedger::whereDate('transaction_date', '>=', $weekStart)->count();
        $lastWeekCount = StockLedger::whereBetween('transaction_date', [$lastWeekStart, $lastWeekEnd])->count();
        $weekGrowth = $lastWeekCount > 0 ? (($thisWeekCount - $lastWeekCount) / $lastWeekCount) * 100 : 0;

        // Total item terdaftar dan aktif
        $totalItems = RawMaterial::where('is_active', true)->count();
        $itemsWithStock = MaterialStock::where('current_stock', '>', 0)
            ->distinct('raw_material_id')
            ->count('raw_material_id');
        $itemsPercentage = $totalItems > 0 ? ($itemsWithStock / $totalItems) * 100 : 0;

        // Stock accuracy dari opname terakhir
        $lastOpname = StockOpname::where('status', 'finalized')
            ->with('details')
            ->latest('finalized_at')
            ->first();

        $accuracyRate = 100;
        $accuracyDescription = 'Belum ada opname';
        
        if ($lastOpname) {
            $totalItems = $lastOpname->details->count();
            $accurateItems = $lastOpname->details->filter(function ($detail) {
                return abs($detail->difference_qty) < 0.01;
            })->count();
            
            $accuracyRate = $totalItems > 0 ? ($accurateItems / $totalItems) * 100 : 100;
            $accuracyDescription = 'Dari opname terakhir (' . $lastOpname->finalized_at->diffForHumans() . ')';
        }

        // Inventory value at risk (stok kritis x harga)
        $valueAtRisk = MaterialStock::whereRaw('current_stock <= min_stock')
            ->where('min_stock', '>', 0)
            ->sum(DB::raw('current_stock * current_avg_cost'));

        // Average days to stockout (per-item ratio, then averaged)
        $dailyUsagePerItem = StockLedger::where('direction', 'out')
            ->whereDate('transaction_date', '>=', now()->subDays(30))
            ->select('raw_material_id', DB::raw('AVG(qty) as avg_daily_qty'))
            ->groupBy('raw_material_id')
            ->pluck('avg_daily_qty', 'raw_material_id');

        $currentStockPerItem = MaterialStock::where('current_stock', '>', 0)
            ->select('raw_material_id', 'current_stock')
            ->pluck('current_stock', 'raw_material_id');

        $daysToStockout = 999;
        $ratios = [];
        foreach ($currentStockPerItem as $rmId => $stock) {
            $usage = $dailyUsagePerItem->get($rmId, 0);
            if ($usage > 0) {
                $ratios[] = $stock / $usage;
            }
        }
        if (!empty($ratios)) {
            $daysToStockout = array_sum($ratios) / count($ratios);
        }

        return [
            // Stat 1: Transaksi Hari Ini
            Stat::make('Transaksi Hari Ini', $totalToday)
                ->description("In: {$stockInToday} • Out: {$stockOutToday} • Transfer: {$transfersToday}")
                ->descriptionIcon('heroicon-m-document-text')
                ->color('primary')
                ->chart($this->getTodayHourlyChart()),

            // Stat 2: Aktivitas Minggu Ini
            Stat::make('Aktivitas Minggu Ini', number_format($thisWeekCount, 0))
                ->description(
                    $weekGrowth >= 0
                        ? 'Naik ' . number_format(abs($weekGrowth), 0) . '% dari minggu lalu'
                        : 'Turun ' . number_format(abs($weekGrowth), 0) . '% dari minggu lalu'
                )
                ->descriptionIcon($weekGrowth >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($weekGrowth >= 0 ? 'success' : 'warning'),

            // Stat 3: Item Coverage
            Stat::make('Item dengan Stok', number_format($itemsWithStock, 0) . ' / ' . number_format($totalItems, 0))
                ->description(number_format($itemsPercentage, 1) . '% dari total item terdaftar')
                ->descriptionIcon('heroicon-m-cube')
                ->color($itemsPercentage >= 80 ? 'success' : 'warning'),

            // Stat 4: Stock Accuracy
            Stat::make('Akurasi Stok', number_format($accuracyRate, 1) . '%')
                ->description($accuracyDescription)
                ->descriptionIcon($accuracyRate >= 95 ? 'heroicon-m-check-badge' : 'heroicon-m-exclamation-circle')
                ->color($accuracyRate >= 95 ? 'success' : ($accuracyRate >= 90 ? 'warning' : 'danger')),

            // Stat 5: Nilai Stok Berisiko
            Stat::make('Nilai Stok Berisiko', 'Rp ' . number_format($valueAtRisk, 0, ',', '.'))
                ->description('Item kritis yang perlu restock')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($valueAtRisk > 10000000 ? 'danger' : 'success'),

            // Stat 6: Estimasi Days to Stockout
            Stat::make('Est. Hari Hingga Stok Habis', $daysToStockout > 90 ? '90+' : number_format($daysToStockout, 0))
                ->description('Berdasarkan rata-rata pemakaian 30 hari')
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color($daysToStockout < 30 ? 'danger' : ($daysToStockout < 60 ? 'warning' : 'success')),
        ];
    }

    /**
     * Transaksi per jam hari ini (untuk mini chart)
     */
    private function getTodayHourlyChart(): array
    {
        $currentHour = now()->hour;
        $data = [];

        for ($i = 0; $i <= min($currentHour, 12); $i++) {
            $hourStart = now()->startOfDay()->addHours($i);
            $hourEnd = $hourStart->copy()->addHour();

            $count = StockLedger::whereBetween('created_at', [$hourStart, $hourEnd])
                ->count();

            $data[] = $count;
        }

        return $data;
    }
}
