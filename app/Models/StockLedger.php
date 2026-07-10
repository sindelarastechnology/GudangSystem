<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class StockLedger extends Model
{
    const UPDATED_AT = null;

    protected $fillable = [
        'raw_material_id',
        'warehouse_id',
        'transaction_date',
        'direction',
        'source_type',
        'source_id',
        'qty',
        'unit_cost',
        'running_qty_balance',
        'running_avg_cost',
        'running_asset_value',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'transaction_date' => 'date',
            'qty' => 'decimal:4',
            'unit_cost' => 'decimal:4',
            'running_qty_balance' => 'decimal:4',
            'running_avg_cost' => 'decimal:4',
            'running_asset_value' => 'decimal:2',
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

    public function source(): MorphTo
    {
        return $this->morphTo();
    }
}
