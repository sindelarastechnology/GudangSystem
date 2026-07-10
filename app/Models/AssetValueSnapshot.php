<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetValueSnapshot extends Model
{
    const UPDATED_AT = null;

    protected $fillable = [
        'snapshot_date',
        'warehouse_id',
        'raw_material_id',
        'qty',
        'avg_cost',
        'asset_value',
    ];

    protected $casts = [
        'snapshot_date' => 'date',
        'qty' => 'decimal:4',
        'avg_cost' => 'decimal:4',
        'asset_value' => 'decimal:2',
    ];

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function rawMaterial(): BelongsTo
    {
        return $this->belongsTo(RawMaterial::class);
    }
}
