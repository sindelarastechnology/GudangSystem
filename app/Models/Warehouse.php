<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use LogicException;

class Warehouse extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'code', 'location', 'is_active', 'is_locked', 'locked_by_opname_id', 'locked_at'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_locked' => 'boolean',
            'locked_at' => 'datetime',
        ];
    }

    public function forceDelete(): never
    {
        throw new LogicException('Warehouse cannot be hard-deleted. Use soft delete instead.');
    }

    public function materialStocks(): HasMany
    {
        return $this->hasMany(MaterialStock::class);
    }

    public function stockInTransactions(): HasMany
    {
        return $this->hasMany(StockInTransaction::class);
    }

    public function stockOutTransactions(): HasMany
    {
        return $this->hasMany(StockOutTransaction::class);
    }

    public function stockTransfersFrom(): HasMany
    {
        return $this->hasMany(StockTransfer::class, 'from_warehouse_id');
    }

    public function stockTransfersTo(): HasMany
    {
        return $this->hasMany(StockTransfer::class, 'to_warehouse_id');
    }

    public function stockOpnames(): HasMany
    {
        return $this->hasMany(StockOpname::class);
    }

    public function stockLedgers(): HasMany
    {
        return $this->hasMany(StockLedger::class);
    }

    public function lockedByOpname(): BelongsTo
    {
        return $this->belongsTo(StockOpname::class, 'locked_by_opname_id');
    }
}
