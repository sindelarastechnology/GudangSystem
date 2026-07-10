<?php

namespace App\Notifications;

use App\Models\MaterialStock;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class LowStockNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public MaterialStock $stock,
    ) {
        $this->stock->loadMissing(['rawMaterial.unit', 'warehouse']);
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => 'Stok Menipis',
            'body' => sprintf(
                'Stok %s di %s telah mencapai %s %s (minimum: %s %s)',
                $this->stock->rawMaterial->name,
                $this->stock->warehouse->name,
                number_format($this->stock->current_stock, 2),
                $this->stock->rawMaterial->unit->symbol,
                number_format($this->stock->min_stock, 2),
                $this->stock->rawMaterial->unit->symbol
            ),
            'material_stock_id' => $this->stock->id,
            'raw_material_id' => $this->stock->raw_material_id,
            'raw_material_code' => $this->stock->rawMaterial->code,
            'raw_material_name' => $this->stock->rawMaterial->name,
            'warehouse_id' => $this->stock->warehouse_id,
            'warehouse_name' => $this->stock->warehouse->name,
            'current_stock' => $this->stock->current_stock,
            'min_stock' => $this->stock->min_stock,
            'unit_symbol' => $this->stock->rawMaterial->unit->symbol,
            'action_url' => route('filament.admin.resources.raw-materials.edit', [
                'record' => $this->stock->raw_material_id,
            ]),
        ];
    }

}
