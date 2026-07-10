<?php

namespace App\Filament\Widgets;

use App\Models\StockInTransaction;
use App\Models\StockOutTransaction;
use App\Models\StockTransfer;
use App\Models\StockOpname;
use Filament\Widgets\Widget;

class RecentActivityWidget extends Widget
{
    protected static ?int $sort = 6;

    protected int | string | array $columnSpan = 'full';

    protected static string $view = 'filament.widgets.recent-activity';

    public function getActivities(): array
    {
        $activities = collect();

        // Stock In - 5 terakhir
        $stockIns = StockInTransaction::with(['warehouse', 'createdBy', 'details.rawMaterial'])
            ->latest()
            ->limit(5)
            ->get()
            ->map(function ($item) {
                $itemCount = $item->details->count();
                $itemNames = $item->details->take(2)->pluck('rawMaterial.name')->implode(', ');
                
                return [
                    'type' => 'stock_in',
                    'type_label' => 'Stock In',
                    'icon' => 'heroicon-m-arrow-down-tray',
                    'color' => 'success',
                    'number' => $item->transaction_number,
                    'date' => $item->transaction_date->format('d M Y'),
                    'warehouse' => $item->warehouse->name,
                    'details' => $itemCount . ' item: ' . ($itemCount > 2 ? $itemNames . ', ...' : $itemNames),
                    'user' => $item->createdBy->name ?? '-',
                    'time' => $item->created_at->diffForHumans(),
                    'created_at' => $item->created_at,
                ];
            });

        // Stock Out - 5 terakhir
        $stockOuts = StockOutTransaction::with(['warehouse', 'createdBy', 'details.rawMaterial'])
            ->latest()
            ->limit(5)
            ->get()
            ->map(function ($item) {
                $itemCount = $item->details->count();
                $itemNames = $item->details->take(2)->pluck('rawMaterial.name')->implode(', ');
                
                return [
                    'type' => 'stock_out',
                    'type_label' => 'Stock Out',
                    'icon' => 'heroicon-m-arrow-up-tray',
                    'color' => 'danger',
                    'number' => $item->transaction_number,
                    'date' => $item->transaction_date->format('d M Y'),
                    'warehouse' => $item->warehouse->name,
                    'details' => $itemCount . ' item: ' . ($itemCount > 2 ? $itemNames . ', ...' : $itemNames),
                    'user' => $item->createdBy->name ?? '-',
                    'time' => $item->created_at->diffForHumans(),
                    'created_at' => $item->created_at,
                ];
            });

        // Transfers - 5 terakhir
        $transfers = StockTransfer::with(['fromWarehouse', 'toWarehouse', 'createdBy', 'details.rawMaterial'])
            ->latest()
            ->limit(5)
            ->get()
            ->map(function ($item) {
                $itemCount = $item->details->count();
                $itemNames = $item->details->take(2)->pluck('rawMaterial.name')->implode(', ');
                
                return [
                    'type' => 'transfer',
                    'type_label' => 'Transfer',
                    'icon' => 'heroicon-m-arrows-right-left',
                    'color' => 'info',
                    'number' => $item->transfer_number,
                    'date' => $item->transfer_date->format('d M Y'),
                    'warehouse' => $item->fromWarehouse->name . ' → ' . $item->toWarehouse->name,
                    'details' => $itemCount . ' item: ' . ($itemCount > 2 ? $itemNames . ', ...' : $itemNames),
                    'user' => $item->createdBy->name ?? '-',
                    'time' => $item->created_at->diffForHumans(),
                    'created_at' => $item->created_at,
                ];
            });

        // Opnames - 5 terakhir
        $opnames = StockOpname::with(['warehouse', 'createdBy'])
            ->latest()
            ->limit(5)
            ->get()
            ->map(function ($item) {
                return [
                    'type' => 'opname',
                    'type_label' => 'Opname',
                    'icon' => 'heroicon-m-clipboard-document-check',
                    'color' => 'warning',
                    'number' => $item->opname_number,
                    'date' => $item->opname_date->format('d M Y'),
                    'warehouse' => $item->warehouse->name,
                    'details' => 'Status: ' . ucfirst($item->status),
                    'user' => $item->createdBy->name ?? '-',
                    'time' => $item->created_at->diffForHumans(),
                    'created_at' => $item->created_at,
                ];
            });

        // Merge dan sort by created_at
        return $activities
            ->concat($stockIns)
            ->concat($stockOuts)
            ->concat($transfers)
            ->concat($opnames)
            ->sortByDesc('created_at')
            ->take(20)
            ->values()
            ->toArray();
    }
}
