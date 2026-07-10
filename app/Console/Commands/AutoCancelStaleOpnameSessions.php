<?php

namespace App\Console\Commands;

use App\Models\StockOpname;
use App\Services\StockOpnameService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class AutoCancelStaleOpnameSessions extends Command
{
    protected $signature = 'opname:auto-cancel-stale';
    
    protected $description = 'Auto-cancel stock opname sessions that have been counting for too long';

    public function __construct(
        private StockOpnameService $opnameService
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $staleHours = config('warehouse.opname_stale_hours', 24);
        $cutoffTime = now()->subHours($staleHours);

        $staleSessions = StockOpname::where('status', 'counting')
            ->where('started_at', '<', $cutoffTime)
            ->with(['warehouse', 'createdBy'])
            ->get();

        if ($staleSessions->isEmpty()) {
            $this->info('No stale opname sessions found.');
            return Command::SUCCESS;
        }

        $this->info("Found {$staleSessions->count()} stale opname session(s). Cancelling...");

        foreach ($staleSessions as $session) {
            try {
                $this->opnameService->cancel($session);
                
                $this->info("✓ Cancelled: {$session->opname_number} at {$session->warehouse->name}");
                
                Log::info("Auto-cancelled stale opname session", [
                    'opname_id' => $session->id,
                    'opname_number' => $session->opname_number,
                    'warehouse_id' => $session->warehouse_id,
                    'warehouse_name' => $session->warehouse->name,
                    'started_at' => $session->started_at,
                    'hours_elapsed' => $session->started_at->diffInHours(now()),
                ]);
                
            } catch (\Exception $e) {
                $this->error("✗ Failed to cancel {$session->opname_number}: {$e->getMessage()}");
                
                Log::error("Failed to auto-cancel stale opname session", [
                    'opname_id' => $session->id,
                    'opname_number' => $session->opname_number,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
            }
        }

        $this->info('Stale opname sessions processing complete.');
        return Command::SUCCESS;
    }
}
