<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use LogicException;

class RawMaterial extends Model
{
    use SoftDeletes;

    protected $fillable = ['code', 'name', 'material_category_id', 'unit_id', 'image', 'is_active'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function forceDelete(): never
    {
        throw new LogicException('RawMaterial cannot be hard-deleted. Use soft delete instead.');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(MaterialCategory::class, 'material_category_id');
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function materialStocks(): HasMany
    {
        return $this->hasMany(MaterialStock::class);
    }

    public function unitConversions(): HasMany
    {
        return $this->hasMany(UnitConversion::class);
    }

    public function stockLedgers(): HasMany
    {
        return $this->hasMany(StockLedger::class);
    }

    public function stockInDetails(): HasMany
    {
        return $this->hasMany(StockInDetail::class);
    }

    public function stockOutDetails(): HasMany
    {
        return $this->hasMany(StockOutDetail::class);
    }

    public function stockTransferDetails(): HasMany
    {
        return $this->hasMany(StockTransferDetail::class);
    }

    public function stockOpnameDetails(): HasMany
    {
        return $this->hasMany(StockOpnameDetail::class);
    }

    public function assetValueSnapshots(): HasMany
    {
        return $this->hasMany(AssetValueSnapshot::class);
    }
}
