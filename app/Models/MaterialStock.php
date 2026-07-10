<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaterialStock extends Model
{
    protected $fillable = [
        'raw_material_id',
        'warehouse_id',
        'min_stock',
        'current_stock',
        'current_avg_cost',
        'current_asset_value',
        'last_notified_at',
    ];

    protected function casts(): array
    {
        return [
            'min_stock' => 'decimal:4',
            'current_stock' => 'decimal:4',
            'current_avg_cost' => 'decimal:4',
            'current_asset_value' => 'decimal:2',
            'last_notified_at' => 'datetime',
        ];
    }

    public function rawMaterial(): BelongsTo
    {
        return $this->belongsTo(RawMaterial::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }
}
