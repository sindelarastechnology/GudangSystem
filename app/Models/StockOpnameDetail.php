<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockOpnameDetail extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'stock_opname_id',
        'raw_material_id',
        'system_qty',
        'physical_qty_unit_id',
        'physical_qty',
        'physical_qty_base',
        'difference_qty',
        'avg_cost_at_opname',
        'difference_value',
        'notes',
    ];

    protected $casts = [
        'system_qty' => 'decimal:4',
        'physical_qty' => 'decimal:4',
        'physical_qty_base' => 'decimal:4',
        'difference_qty' => 'decimal:4',
        'avg_cost_at_opname' => 'decimal:4',
        'difference_value' => 'decimal:2',
    ];

    public function stockOpname(): BelongsTo
    {
        return $this->belongsTo(StockOpname::class);
    }

    public function rawMaterial(): BelongsTo
    {
        return $this->belongsTo(RawMaterial::class);
    }

    public function physicalQtyUnit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'physical_qty_unit_id');
    }
}
