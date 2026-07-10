<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockOutDetail extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'stock_out_transaction_id',
        'raw_material_id',
        'unit_id',
        'qty',
        'qty_base',
        'cost_at_issue',
        'subtotal_hpp',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'qty' => 'decimal:4',
            'qty_base' => 'decimal:4',
            'cost_at_issue' => 'decimal:4',
            'subtotal_hpp' => 'decimal:2',
        ];
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(StockOutTransaction::class, 'stock_out_transaction_id');
    }

    public function rawMaterial(): BelongsTo
    {
        return $this->belongsTo(RawMaterial::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }
}
