<?php

namespace App\Console\Commands;

use App\Models\MaterialStock;
use App\Models\User;
use App\Notifications\LowStockNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class DailyLowStockDigest extends Command
{
    protected $signature = 'stock:daily-low-stock-digest';
    
    protected $description = 'Send daily digest of all items with low stock';

    public function handle(): int
    {
        $cooldownDays = config('warehouse.low_stock_notification_cooldown_days', 3);
        $cutoffDate = now()->subDays($cooldownDays);

        $lowStocks = MaterialStock::where('current_stock', '<=', \DB::raw('min_stock'))
            ->where(function ($query) use ($cutoffDate) {
                $query->whereNull('last_notified_at')
                    ->orWhere('last_notified_at', '<', $cutoffDate);
            })
            ->with(['rawMaterial.unit', 'warehouse'])
            ->get();

        if ($lowStocks->isEmpty()) {
            $this->info('No low stock items found.');
            return Command::SUCCESS;
        }

        $this->info("Found {$lowStocks->count()} low stock item(s). Sending digest...");

        $users = User::whereHas('roles', function ($q) {
            $q->whereIn('name', ['super_admin', 'admin']);
        })->get();

        if ($users->isEmpty()) {
            $this->warn('No users found to notify.');
            return Command::SUCCESS;
        }

        foreach ($lowStocks as $stock) {
            try {
                Notification::send($users, new LowStockNotification($stock));
                
                $stock->update(['last_notified_at' => now()]);

                $this->info(sprintf(
                    '✓ Notified: %s at %s (Stock: %s %s, Min: %s %s)',
                    $stock->rawMaterial->name,
                    $stock->warehouse->name,
                    number_format($stock->current_stock, 2),
                    $stock->rawMaterial->unit->symbol,
                    number_format($stock->min_stock, 2),
                    $stock->rawMaterial->unit->symbol
                ));

                Log::info('Daily low stock notification sent', [
                    'material_stock_id' => $stock->id,
                    'raw_material_name' => $stock->rawMaterial->name,
                    'warehouse_name' => $stock->warehouse->name,
                    'current_stock' => $stock->current_stock,
                    'min_stock' => $stock->min_stock,
                ]);

            } catch (\Exception $e) {
                $this->error("✗ Failed to notify for {$stock->rawMaterial->name}: {$e->getMessage()}");
                
                Log::error('Failed to send daily low stock notification', [
                    'material_stock_id' => $stock->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->info('Daily low stock digest complete.');
        return Command::SUCCESS;
    }
}
