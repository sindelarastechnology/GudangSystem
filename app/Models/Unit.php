<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Unit extends Model
{
    protected $fillable = ['name', 'symbol'];

    public function rawMaterials(): HasMany
    {
        return $this->hasMany(RawMaterial::class, 'unit_id');
    }

    public function conversionsFrom(): HasMany
    {
        return $this->hasMany(UnitConversion::class, 'from_unit_id');
    }

    public function conversionsTo(): HasMany
    {
        return $this->hasMany(UnitConversion::class, 'to_unit_id');
    }

    public function stockInDetails(): HasMany
    {
        return $this->hasMany(StockInDetail::class, 'unit_id');
    }

    public function stockOutDetails(): HasMany
    {
        return $this->hasMany(StockOutDetail::class, 'unit_id');
    }

    public function stockTransferDetails(): HasMany
    {
        return $this->hasMany(StockTransferDetail::class, 'unit_id');
    }
}
