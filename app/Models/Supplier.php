<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use LogicException;

class Supplier extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'phone', 'address', 'is_active'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function forceDelete(): never
    {
        throw new LogicException('Supplier cannot be hard-deleted. Use soft delete instead.');
    }

    public function stockInTransactions(): HasMany
    {
        return $this->hasMany(StockInTransaction::class);
    }
}
