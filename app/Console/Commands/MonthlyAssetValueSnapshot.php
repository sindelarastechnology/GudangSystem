<?php

namespace App\Console\Commands;

use App\Models\AssetValueSnapshot;
use App\Models\MaterialStock;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class MonthlyAssetValueSnapshot extends Command
{
    protected $signature = 'snapshot:monthly-asset-value';

    protected $description = 'Take a monthly snapshot of asset values from all material stocks';

    public function handle(): int
    {
        $snapshotDate = now()->subMonth()->endOfMonth()->startOfDay();

        $existingCount = AssetValueSnapshot::where('snapshot_date', $snapshotDate)->count();
        if ($existingCount > 0) {
            $this->warn("Snapshot for {$snapshotDate->format('Y-m-d')} already exists ({$existingCount} records). Skipping.");
            return Command::SUCCESS;
        }

        $stocks = MaterialStock::with(['warehouse', 'rawMaterial.unit'])->get();

        if ($stocks->isEmpty()) {
            $this->warn('No material stocks found. Snapshot would be empty.');
            return Command::SUCCESS;
        }

        $records = [];
        foreach ($stocks as $stock) {
            $records[] = [
                'snapshot_date' => $snapshotDate,
                'warehouse_id' => $stock->warehouse_id,
                'raw_material_id' => $stock->raw_material_id,
                'qty' => $stock->current_stock,
                'avg_cost' => $stock->current_avg_cost,
                'asset_value' => $stock->current_asset_value,
            ];
        }

        AssetValueSnapshot::insert($records);

        $this->info("Monthly snapshot created: {$snapshotDate->format('Y-m-d')} — {$stocks->count()} records.");

        Log::info('Monthly asset value snapshot created', [
            'snapshot_date' => $snapshotDate->format('Y-m-d'),
            'record_count' => $stocks->count(),
        ]);

        return Command::SUCCESS;
    }
}
