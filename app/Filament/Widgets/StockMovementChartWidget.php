<?php

namespace App\Filament\Widgets;

use App\Models\StockInTransaction;
use App\Models\StockOutTransaction;
use App\Models\StockTransfer;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class StockMovementChartWidget extends ChartWidget
{
    protected static ?string $heading = 'Pergerakan Stok - 6 Bulan Terakhir';

    protected static ?string $description = 'Analisis barang masuk vs keluar untuk monitoring inventory turnover';

    protected static ?int $sort = 4;

    protected int | string | array $columnSpan = 'full';

    protected static ?string $maxHeight = '300px';

    public ?string $filter = 'all';

    protected function getData(): array
    {
        $months = [];
        $stockInData = [];
        $stockOutData = [];
        $netMovementData = [];

        // Generate data untuk 6 bulan terakhir
        for ($i = 5; $i >= 0; $i--) {
            $monthStart = now()->subMonths($i)->startOfMonth();
            $monthEnd = now()->subMonths($i)->endOfMonth();
            $monthLabel = $monthStart->format('M Y');
            
            $months[] = $monthLabel;

            // Total barang masuk (dalam satuan dasar qty_base)
            $stockIn = DB::table('stock_in_details')
                ->join('stock_in_transactions', 'stock_in_details.stock_in_transaction_id', '=', 'stock_in_transactions.id')
                ->whereBetween('stock_in_transactions.transaction_date', [$monthStart, $monthEnd])
                ->when($this->filter !== 'all', function ($query) {
                    return $query->where('stock_in_transactions.warehouse_id', $this->filter);
                })
                ->sum('stock_in_details.qty_base');

            // Total barang keluar (dalam satuan dasar qty_base)
            $stockOut = DB::table('stock_out_details')
                ->join('stock_out_transactions', 'stock_out_details.stock_out_transaction_id', '=', 'stock_out_transactions.id')
                ->whereBetween('stock_out_transactions.transaction_date', [$monthStart, $monthEnd])
                ->when($this->filter !== 'all', function ($query) {
                    return $query->where('stock_out_transactions.warehouse_id', $this->filter);
                })
                ->sum('stock_out_details.qty_base');

            // Total transfer keluar/masuk
            $transferOut = DB::table('stock_transfer_details')
                ->join('stock_transfers', 'stock_transfer_details.stock_transfer_id', '=', 'stock_transfers.id')
                ->whereBetween('stock_transfers.transfer_date', [$monthStart, $monthEnd])
                ->when($this->filter !== 'all', function ($query) {
                    return $query->where('stock_transfers.from_warehouse_id', $this->filter);
                })
                ->sum('stock_transfer_details.qty_base');

            $transferIn = DB::table('stock_transfer_details')
                ->join('stock_transfers', 'stock_transfer_details.stock_transfer_id', '=', 'stock_transfers.id')
                ->whereBetween('stock_transfers.transfer_date', [$monthStart, $monthEnd])
                ->when($this->filter !== 'all', function ($query) {
                    return $query->where('stock_transfers.to_warehouse_id', $this->filter);
                })
                ->sum('stock_transfer_details.qty_base');

            $stockOut += $transferOut;
            $stockIn += $transferIn;

            $stockInData[] = round($stockIn, 2);
            $stockOutData[] = round($stockOut, 2);
            $netMovementData[] = round($stockIn - $stockOut, 2);
        }

        return [
            'datasets' => [
                [
                    'label' => 'Barang Masuk',
                    'data' => $stockInData,
                    'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                    'borderColor' => '#10b981',
                    'borderWidth' => 2,
                    'fill' => 'start',
                    'tension' => 0.4,
                ],
                [
                    'label' => 'Barang Keluar',
                    'data' => $stockOutData,
                    'backgroundColor' => 'rgba(244, 63, 94, 0.05)',
                    'borderColor' => '#f43f5e',
                    'borderWidth' => 2,
                    'fill' => 'start',
                    'tension' => 0.4,
                ],
                [
                    'label' => 'Net Movement',
                    'data' => $netMovementData,
                    'type' => 'line',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.3)',
                    'borderColor' => 'rgb(59, 130, 246)',
                    'borderWidth' => 3,
                    'fill' => true,
                    'tension' => 0.4,
                ],
            ],
            'labels' => $months,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'bottom',
                ],
                'tooltip' => [
                    'enabled' => true,
                    'mode' => 'index',
                    'intersect' => false,
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'callback' => 'function(value) { return value.toLocaleString(); }',
                    ],
                ],
            ],
            'interaction' => [
                'mode' => 'nearest',
                'axis' => 'x',
                'intersect' => false,
            ],
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
